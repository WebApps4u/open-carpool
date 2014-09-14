"""
Open CarPool is a free and open source carpooling/dynamic ride sharing 
system, open to be connected to other car pools and public transport.
Copyright (C) 2009-2014  Julian Rath, Oliver Pintat

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

In case this software interacts with other software tools, other 
licenses may apply.


Module for the core functionality for the call2ride pilot and Open CarPool
"""


import psycopg2
import datetime
import pytz
import logging
from sms_providers import sms_trade_de
import md5



DEFAULT_COUNTRY_CODE = '49'
## Max Minutes a time can be in the past before adding a day
MINUTES_DELTA = 10
logger = logging.getLogger(__name__)


def sms_mail(receiver, message, config):

  logger.info("sending mail: %s" % message)
   
  # Import smtplib for the actual sending function
  import smtplib

  # Import the email modules we'll need
  from email.mime.text import MIMEText

  # Open a plain text file for reading.  For this example, assume that
  # the text file contains only ASCII characters.
  #fp = open(textfile, 'rb')
  # Create a text/plain message
  msg = MIMEText(message)
  #fp.close()

  # me == the sender's email address
  # you == the recipient's email address
  msg['Subject'] = 'Call2Ride Text Message to %s' % receiver
  msg['From'] = 'sms@call2ride.org'
  msg['To'] = 'crath@ebay.de'

  # Send the message via our own SMTP server, but don't include the
  # envelope header.
  s = smtplib.SMTP(config.get('mail', 'smtp'))
  s.ehlo()
  s.login(config.get('mail', 'user'), config.get('mail', 'password'))
  s.sendmail(msg['From'], [msg['To']], msg.as_string())
  s.quit()


def c2r_check_appointment_in_future(start_date, tm, tzname):
    ## if no date, then result is always true
    if start_date==None or start_date=='':
      return True

    time = datetime.time(int(tm[0:2]), int(tm[2:4]))
    date = datetime.date(int(start_date[0:4]),int(start_date[4:6]),int(start_date[6:8]))
    tz = pytz.timezone(tzname)
    now = datetime.datetime.now(tz)
    timenow = datetime.time(now.hour, now.minute)
    timeadd = datetime.timedelta(minutes=MINUTES_DELTA)
    timeplus = now - timeadd
    timeplus2 = datetime.time(timeplus.hour, timeplus.minute)
    resultdate = datetime.datetime.combine(date, time)

    if resultdate > now.replace(tzinfo=None):
        return True

    return False

def c2r_time_to_datetime(start_date, tm, tzname, checkonly=False, force=False):
    """
    @param tm: 4 digit str
    @return: datetime.datetime object with date of today and combined time
    """
    time = datetime.time(int(tm[0:2]), int(tm[2:4]))
    
    date = datetime.date.today()
    start_date_bool = True
    if start_date!=None and start_date!='':
      date = datetime.date(int(start_date[0:4]),int(start_date[4:6]),int(start_date[6:8]))
      start_date_bool = False

    tz = pytz.timezone(tzname)

    now = datetime.datetime.now(tz)
    timenow = datetime.time(now.hour, now.minute)
    timeadd = datetime.timedelta(minutes=MINUTES_DELTA)
    timeplus = now - timeadd
    timeplus2 = datetime.time(timeplus.hour, timeplus.minute)
    resultdate = datetime.datetime.combine(date, time)
    ## Check Datetime, if in the past use tomorrow
    if (timeplus2 > time or force) and start_date_bool:
      if checkonly:
        return True
      aday = datetime.timedelta(days=1)
      resultdate = resultdate + aday

    if checkonly:
      return False
    resultdate = tz.localize(resultdate)
    return resultdate

def normalize_caller_id(clip):
    """
    @raise: ValueError if erong format
    This convert a clip (eg 0712123123123, +49712123123123 or
    0049712123123123) to the international form starting with +XX.
    If the number is starting with only one 0 zero, germany(+49) is assumed.
    """
    clip = clip.strip()
    if clip.startswith('+'):
        numerical_rep = clip[1:]
    elif clip.startswith('00'):
        numerical_rep = clip
    elif clip.startswith('0'):
        numerical_rep = DEFAULT_COUNTRY_CODE+clip[1:]
    else:
        raise ValueError, 'CLIP format unsupported "%"' % clip
    ##test if it is a real number
    numerical_rep = long(numerical_rep)
    return '+%s' % ( numerical_rep)


def verify_user_id(user_id, db_con):
    """
    verify the format of the id and if it is existing in database
    """
    user_id = int(user_id)
    cur = db_con.cursor()
    cur.execute("SELECT NULL FROM users WHERE id = %d" % user_id)
    cur.fetchone()
    if cur.rowcount == 1:
        return True
    else:
        return False

def verify_user_number_id(user_number_id, db_con):
    """
    verify the format of the id and if it is existing in database
    """
    user_number_id = int(user_number_id)
    cur = db_con.cursor()
    cur.execute("SELECT NULL FROM user_number WHERE id = %d" % user_number_id)
    cur.fetchone()
    if cur.rowcount == 1:
        return True
    else:
        return False

def verify_route_id(route_id, db_con):
    """
    verify the format of the id and if it is existing in database
    """
    user_id = int(route_id)
    cur = db_con.cursor()
    cur.execute("SELECT NULL FROM routes WHERE id = %d" % route_id)
    cur.fetchone()
    if cur.rowcount == 1:
        return True
    else:
        return False

def get_user_id_from_number_id(number_id, db_con):
    cur = db_con.cursor()
    sql = 'select user_id from user_number where id = %d'
    cur.execute(sql % number_id)
    user_id, = cur.fetchone()
    cur.close()
    return user_id

def get_active_offer_ids(user_number_id, route_id, reverse, db_con, start_date=None):
    """
    returns the latest active offer ids
    """
    user_id = get_user_id_from_number_id(user_number_id, db_con)

    date = datetime.date.today()
    if start_date!=None and start_date!='':
      date = datetime.date(int(start_date[0:4]),int(start_date[4:6]),int(start_date[6:8]))

    sqldate = ' date_trunc(\'day\', start_time)=TIMESTAMP \'%s\' and ' % date.strftime('%Y-%m-%d')

    cur = db_con.cursor()
    rev = 'reverse=FALSE'
    if reverse == 1:
      rev = 'reverse=TRUE'
    sql = "SELECT id FROM ride_offers WHERE %s user_number_id in (select id from user_number where user_id = %s) AND  route_id = %s AND status = 'open' and %s" % (sqldate, user_id, route_id, rev)
    cur.execute(sql)
    res = cur.fetchall()
    if res != []:
        return [c[0] for c in res]
    else:
        return None


def get_active_request_ids(user_number_id, start_point, end_point, db_con, start_date=None):
    """
    retruns the latest active offer ids
    """
    date = datetime.date.today()
    if start_date!=None and start_date!='':
      date = datetime.date(int(start_date[0:4]),int(start_date[4:6]),int(start_date[6:8]))
    sqldate = ' date_trunc(\'day\', earliest_start_time)=TIMESTAMP \'%s\' and ' % date.strftime('%Y-%m-%d')

    user_id = get_user_id_from_number_id(user_number_id, db_con)
    cur = db_con.cursor()
    cur.execute("SELECT id FROM ride_requests WHERE %s user_number_id in (select id from user_number where user_id = %s) AND  start_point = %s AND end_point = %s AND status = 'open'" % (sqldate, user_id, start_point, end_point))
    res = cur.fetchall()
    if res != []:
        return [c[0] for c in res]
    else:
        return None



class Call2RideError(Exception):
    """
    General call2ride exception
    """
    pass

class Call2Ride(object):
    """
    Main class implementing core functions for call2ride, may offered by
    webservices or used directly
    """
    
    _INSERT_OFFER_STATMENT = '''INSERT INTO ride_offers
      (user_number_id,  route_id, start_time, status, reverse) VALUES(%i, %i, '%s', 'open', %d=1)'''
    
    _INSERT_REQUEST_STATEMENT = '''INSERT INTO ride_requests 
     (user_number_id, start_point, end_point, earliest_start_time, latest_start_time, status) 
     VALUES(%i, %i, %i, '%s', '%s', 'open')'''
    
    _SELECT_CELL_STATMENT = '''SELECT cell_phone_nr FROM users 
      WHERE id = %i'''
    
    _LOG_SMS_STATEMENT = '''INSERT INTO 
    sms(message_id, message, receiver_nr, cost, error) VALUES
    ('%s', '%s', '%s', %f, '%s')'''
    
    ##for matching in a request
    _REQ_MATCH_STATEMENT = """
    SELECT ride_offers.start_time, users.name, users.cell_phone_nr
    FROM ride_offers, users, routes WHERE 
    ride_offers.user_id = users.id AND ride_offers.route_id = routes.id
      AND ride_offers.start_time >= TIMESTAMP '%s'
      AND ride_offers.start_time <= TIMESTAMP '%s' 
      AND ride_offers.route_id = %d
      AND ride_offers.status = 'open'
      AND routes.status = 'enabled'
      ORDER by ride_offers.start_time 
      LIMIT 5"""

    ##find possible ride offer
    _POSSIBLE_RIDES_STATEMENT = """
    SELECT id FROM ride_offers WHERE ride_offers.status = 'open' AND route_id in (
    SELECT distinct route_id  FROM route_pickuppoint WHERE 
    point_id=%d
    and route_id in (
    SELECT distinct route_id  FROM route_pickuppoint where point_id=%d
    )
    )
    """
 
    ##reverse matching in a offer
    _OFFER_MATCH_STATEMENT = """
    SELECT users.cell_phone_nr FROM users, ride_requests
      WHERE
    users.id = ride_requests.user_id
    AND ride_requests.earliest_start_time <= TIMESTAMP '%s'
    AND ride_requests.latest_start_time >= TIMESTAMP '%s'
    AND ride_requests.route_id = %s
    AND ride_requests.status = 'open'
    """
    
    _REQ_PUBLIC_TRANSPORT_MATCH = """SELECT departure_time, means FROM
    public_transport WHERE public_transport.departure_time + current_date >= '%s' AND
      public_transport.departure_time + current_date<= '%s' AND
      public_transport.start_point = %d AND end_point = %d ORDER BY departure_time LIMIT 2"""
    
    ##closes all requests on this route before timstamp

    
    ##log statement needs to be callled before the normal
    _REQ_CLOSE_LOG_STATEMENT = """INSERT INTO 
     status_log(ride_requests_id, old_status, new_status)
     VALUES
     (%d, 'open', 'closed')"""
    
    _REQ_CLOSE_STATEMENT = """UPDATE ride_requests
      SET status = 'closed'
      WHERE id=%d"""

    ##log statement needs to be callled before the normal
    _OFFER_CLOSE_LOG_STATEMENT = """INSERT INTO 
    status_log(ride_offer_id, old_status, new_status)
    VALUES(
      %d,
     'open', 'closed')"""

    _OFFER_CLOSE_STATEMENT = """UPDATE ride_offers
      SET status = 'closed'
      WHERE id = %d and reverse=(1=%d)"""
    
    
    _GET_ROUTES_STATEMENT = """SELECT id FROM routes WHERE status = 'enabled'"""
    
    
    _GET_TAXI_NUMBER_STATEMENT = """SELECT number FROM taxi WHERE location_id = %d"""
    ##i18n?
    _SMS_FOUND_TEXT = u"""Your next rides on route #%s (%s) are:
%s
"""
    
    _SMS_REQ_TAXI_TEXT = """or call a Taxi %s """

    _SMS_OFFER_TEXT = '%s, %s (%s)\n'
    
    _SMS_OFFER_MATCH_TEXT = u"""There is a new ride matching your request\
for  route #%s (%s): %s, %s (%s)."""
   
    _SMS_OFFER_CONFIRMATION = """Thank you for offering a ride on route #%s  (%s) at %s. We will inform potential passengers. Please call again in case you need to reschedule or can not offer any more rides."""


    ## CREATE TABLE notifications (
    ## id bigserial primary key,
    ## user_id bigserial NOT NULL,
    ## message text NOT NULL,
    ## date_matched timestamp with time zone default NULL,
    ## unread boolean default true); 

    _GET_NOTIFICATIONS = """SELECT message FROM notifications WHERE user_id = %d and unread=true"""

    _INSERT_NOTIFICATIONS_STATMENT = '''INSERT INTO notifications
                  (user_id, message, date_matched, unread) VALUES(%d, '%s', now(), true)'''

    _UPDATE_NOTIFICATIONS_STATMENT = '''UPDATE notifications set unread=false where id = %d'''

    def __init__(self, sms_provider, config, timeformat='%d %b %H:%M'):
        """
        @param sms_provider: a provider for sending sms eg call_trade_de.py
        @param config: ConfigParser Object
        """
        self._timeformat = timeformat
        self._sms_provider = sms_provider
        self._dsn = config.get('database', 'connection') 
        self._config = config
        logger.debug('initialized interface with dsn "%s"' % self._dsn)
    
    def _get_cell_phone_nr(self, user_id):
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        cur.execute(self._SELECT_CELL_STATMENT % user_id)
        return cur.fetchone()[0]
    
    def _send_sms(self, receiver, message):
        
        logger.debug("_send_sms with message %s" %message)

        """
        Sends a sms via the sms provider and create the relating database 
        entry to it
        """
        res = None
        try:
            res = self._sms_provider.send_sms(receiver, message)
            error = 'NULL'
            logger.debug("sms response %s" % res)
        except sms_trade_de.SmsGatewayError, err:
            error = "'%s'" % str(err)
            logger.debug("sms error %s" % error)
            import smtplib
            from email.mime.text import MIMEText
            msg = MIMEText(message)
            msg['Subject'] = 'Open CarPool SMS Error %s' % error
            msg['From'] = 'sms@call2ride.org'
            msg['To'] = 'mail@opencarpool.org'
            s = smtplib.SMTP(self._config.get('mail', 'smtp'))
            s.ehlo()
            s.login(self._config.get('mail', 'user'), self._config.get('mail', 'password'))
            s.sendmail(msg['From'], [msg['To']], msg.as_string())
            s.quit()

        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        if res!=None:
          cur.execute(self._LOG_SMS_STATEMENT % (
                res.get('message_id', 'NO_MESSAGE_ID'),
                message,
                receiver,
                res.get('cost', 0.0),
                error))
          con.commit()
        con.close()
        sms_mail(receiver, message, self._config)
    
    def callid_to_locationid(self, call_id):
        """
        This method is to convert a callid(the telefonnumber which is
        called) to the relating location id.
        """
        raise Call2RideError("NOT_IMPLEMENTED")
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()

        cur.execute(self._XXX)
        if cur.rowcount == 0:
            raise Call2RideError("UNKNOW_ERROR")
 
    def callerid_to_userid(self, caller_id):
        """
        This convert a callerid (eg 0712123123123, +49712123123123 or 
        0049712123123123) in a userid representation of the backend. 
        If the number is starting with only one 0 zero, germany(+49) is 
        assumed. The user_id should be in string format, because the 
        backend may use db-id's, uuid's or openid...
        @param caller_id: str
        @returns: str max lenght 100, user_id
        """
        clip = normalize_caller_id(caller_id)
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        ##cur.execute("SELECT id from users WHERE cell_phone_nr = '%s'" % clip)
        cur.execute("SELECT id from user_number WHERE number = '%s'" % clip)
        if cur.rowcount == 0:
            return -1
        else:
            return cur.fetchone()[0]

    def offer_ride(self, user_number_id, location_id, route_key, start_time, reverse, start_date=None, send_sms=1):
        """
        @param user_id: str with max lenght of 100
        @param route_id: int
        @param start_time: 4 digit integer HHMM
        @returns: None
        """
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        
        ##parameter checking
        if not verify_user_number_id(user_number_id, con):
            raise Call2RideError("ERR_UNKNOWN_USER_ID")
      
        lang = self._get_lang_by_user_number_id(user_number_id)
 
        ## check location_id
        if location_id == 0:
          location_id = self._get_default_location_id(user_number_id)
 
        ##find route id
        # select distinct(r.id) from routes r, user_number un where key='10' and location_id=1 and (r.user_id=0)or (r.user_id=un.user_id and un.id = 17)
        #sql = "select r.id from routes r, user_number un where key='%s' and r.user_id=un.user_id and un.id = %d and location_id=%d"
        sql = "select distinct(r.id) from routes r, user_number un where key='%s' and location_id=%d and ((r.user_id=0) or (r.user_id=un.user_id and un.id = %d))"
        cur.execute(sql % (route_key, location_id, user_number_id))
        res = cur.fetchone()
        if res == None:
          sql = "select id from routes where key='%s' and location_id=%d"
          cur.execute(sql % (route_key, location_id))
          res = cur.fetchone()
          if res == None:
            raise Call2RideError("ERR_UNKNOWN_ROUTE_ID")
        route_id = res[0]
        
        ## check if offer is in the future
        if c2r_check_appointment_in_future(start_date, start_time, self.get_timezone_name(location_id)) == False:
          return 'ERROR: Appointment is in the past'

        ##time to today
        timestamp = c2r_time_to_datetime(start_date, start_time, self.get_timezone_name(location_id))
        self.close_offer(user_number_id, route_id, reverse, False, start_date)
        
        ##insert offer entry into database
        cur.execute(self._INSERT_OFFER_STATMENT % (user_number_id,
          route_id,
          timestamp, reverse))
        con.commit()
        
        ##get name and cellphone nr from user for matching
        driver_name, driver_cellphone = self._get_user_info(user_number_id)
        
        ##reverse matching
        sql = """select id, start_point, end_point, user_number_id from ride_requests where
        start_point in (select point_id from route_pickuppoint where route_id=%d) and
        end_point in (select point_id from route_pickuppoint where route_id=%d) and
        status = 'open'"""
        cur.execute(sql % (route_id, route_id))
        for (aid, astart_point, aend_point, auser_id) in cur.fetchall():
          ## check direction
          direction_check = self._check_direction(route_id, reverse, astart_point, aend_point)
          if direction_check == 1:
            ##get steptime
            #sql2 = 'select steptime from route_pickuppoint where route_id=%d and point_id=%d'
            cur2 = con.cursor()
            #cur2.execute(sql2 % (route_id, astart_point))
            #st, = cur2.fetchone()
            st = self._get_steptime(route_id, astart_point, aend_point)
            sql2 = """select TIMESTAMP WITH TIME ZONE '%s' + time '%s' from ride_requests where id = %d and
            earliest_start_time <= TIMESTAMP WITH TIME ZONE '%s' + time '%s' and
            latest_start_time >= TIMESTAMP WITH TIME ZONE '%s' + time '%s'
            """
            cur2.execute(sql2 % (timestamp, st, aid, timestamp, st, timestamp, st))
            res = cur2.fetchone()
            if res != None:
              point_starttime_db, = res
              point_starttime = self._dbtime_to_localtime(point_starttime_db, self.get_timezone_name(location_id))
              XSMS_OFFER_MATCH_TEXT = u"""There is a new ride matching your request from %s(%s) to %s(%s): %s, %s (%s)."""
              XSMS_OFFER_MATCH_TEXT = self._t(XSMS_OFFER_MATCH_TEXT,lang)
              smstext = XSMS_OFFER_MATCH_TEXT % (
                self._get_pickuppoint_name(astart_point), 
                astart_point, 
                self._get_pickuppoint_name(aend_point), 
                aend_point, 
                point_starttime.strftime(self._timeformat),
                driver_name, 
                driver_cellphone)
              reqester_name, requester_phone = self._get_user_info(auser_id)
              self._send_sms(requester_phone, smstext)
 
        ##send confirmation sms
        sql = 'select name from route_pickuppoint rp, pickuppoint p where p.id=rp.point_id and route_id = %d order by position'
        if reverse == 1:
          sql += ' desc'
        cur.execute(sql % route_id)
        first = 1
        route_names = ''
        for (name,) in cur.fetchall():
          if first == 0:
            route_names += ', '
          route_names += name
          first = 0
        route_names = route_names.decode("utf-8")
	 ## syntax for translation texts: self._t(text, lang) 
        smstext = self._t(self._SMS_OFFER_CONFIRMATION, lang) % (route_key, route_names, timestamp.strftime(self._timeformat))
        if send_sms == 1:
          self._send_sms(driver_cellphone, smstext)

        cur.close()
        con.close()
        return smstext
        
    def request_ride_route(self,
	                 user_number_id,
	                 location_id,
	                 route_key,
	                 reverse,
	                 earliest_start_time,
	                 latest_start_time,
	                 start_date=None,
	                 send_sms=1):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      ##find route id
      sql = "select r.id from routes r, user_number un where key='%s' and r.user_id=un.user_id and un.id = %d and location_id=%d"
      cur.execute(sql % (route_key, user_number_id, location_id))
      res = cur.fetchone()
      if res == None:
        sql = "select id from routes where key='%s' and location_id=%d"
        cur.execute(sql % (route_key, location_id))
        res = cur.fetchone()
        if res == None:
          raise Call2RideError("ERR_UNKNOWN_ROUTE_ID")
      route_id = res[0]

      ##find point id
      rps = self.route_points_get(route_id)
      first = 1
      for (id, rid, pid, steptime, position) in rps:
        if first == 1:
          first_point = pid
        last_point = pid
        first = 0

      ##get keys
      start_key = self._get_point_key(first_point)
      end_key = self._get_point_key(last_point)

      ## reverse
      if reverse:
        tmp = start_key
        start_key = end_key
        end_key = tmp

      return self.request_ride(user_number_id, location_id, start_key, end_key, earliest_start_time, latest_start_time, start_date, send_sms)

    def get_matches_for_offer(self, offer_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      ##find route id
      sql = "select ro.route_id, ro.reverse, to_char(ro.start_time, 'YYYY-Mon-DD HH24:MI:SS'), r.location_id from ride_offers ro, routes r  where ro.id=%d and ro.route_id=r.id"
      cur.execute(sql % offer_id)
      res = cur.fetchone()
      route_id, reverse, timestamp, location_id = res
      l = []
      ##reverse matching
      sql = """select id, start_point, end_point, user_number_id from ride_requests where
      start_point in (select point_id from route_pickuppoint where route_id=%d) and
      end_point in (select point_id from route_pickuppoint where route_id=%d) and
      status = 'open'"""
      cur.execute(sql % (route_id, route_id))
      for (aid, astart_point, aend_point, auser_id) in cur.fetchall():
        ## check direction
        direction_check = self._check_direction(route_id, reverse, astart_point, aend_point)
        if direction_check == 1:
          cur2 = con.cursor()
          st = self._get_steptime(route_id, astart_point, aend_point)
          sql2 = """select id, TIMESTAMP WITH TIME ZONE '%s' + time '%s' from ride_requests where id = %d and
          earliest_start_time <= TIMESTAMP WITH TIME ZONE '%s' + time '%s' and
          latest_start_time >= TIMESTAMP WITH TIME ZONE '%s' + time '%s'
          """
          cur2.execute(sql2 % (timestamp, st, aid, timestamp, st, timestamp, st))
          res = cur2.fetchone()
          if res != None:
            matching_route_id, point_starttime_db, = res
            l.append(matching_route_id)
      return l



  


 
    def get_matches_for_request(self, request_id):
      """
      find all matches for a specific request
      """
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      sql = "select id, to_char(earliest_start_time, 'YYYY-Mon-DD HH24:MI:SS'), to_char(latest_start_time, 'YYYY-Mon-DD HH24:MI:SS'), to_char(request_time, 'YYYY-Mon-DD HH24:MI:SS'), status, start_point, end_point, user_number_id from ride_requests where id=%d"
      cur.execute(sql % request_id)
      res = cur.fetchone()
     
      start_time = res[1]
      latest_start_time = res[2] 
      start_point = res[5]
      end_point = res[6]

      sql = "select id, location_id from pickuppoint where id=%d"
      cur.execute(sql % start_point)
      res_l = cur.fetchone()
      location_id = res_l[1]

      l = []
      offer_text = ''

      cur.execute(self._POSSIBLE_RIDES_STATEMENT % (start_point, end_point))
      for roid, in cur.fetchall():
           cur2 = con.cursor()
           ride_res = self._get_ride_offer_info(roid)
           if ride_res != False:
             route_id, reverse, driver_name, driver_cell_phone_nr = ride_res
             st = self._get_steptime(route_id, start_point, end_point)
             if self._check_direction(route_id, reverse, start_point, end_point):
               sql2 = """SELECT start_time + time '%s'
FROM ride_offers WHERE 
start_time  + time '%s' >= TIMESTAMP WITH TIME ZONE '%s'
AND start_time  + time '%s' <= TIMESTAMP WITH TIME ZONE '%s' AND id = %d"""
               timematch = sql2 % (st, st, start_time, st, latest_start_time, roid)
               cur2.execute(timematch)
               amatch = cur2.fetchone()
               if amatch != None:
                 start_time_db = amatch[0]
                 point_starttime = self._dbtime_to_localtime(start_time_db, self.get_timezone_name(location_id))
                 offer_text += '%s, %s, %s\n' % (point_starttime.strftime(self._timeformat), driver_name, driver_cell_phone_nr)
                 l.append(roid)
                 cur2.close()
                   
      return l
 
    def request_ride(self,
                     user_number_id,
                     location_id,
                     start_key,
                     end_key,
                     earliest_start_time,
                     latest_start_time,
                     start_date=None,
                     send_sms=1):
        """
        This function is for requesting a ride for a route.
        It starts the match process in the backend,
        this may work asynchronous as it doesn't return anything.
        It closes all requests for this user_id and this route_id before
        earliest_start_time.
        @param user_id: backend specific user-id
        @param loaction_id: backaend specific id of the location
        @param start_key: key of the start pickuppoint
        @param end_key: key of the end pickuppoint
        @param earliest_start_time: 4 digit integer HHMM
        @param latest_start_time: 4 digit integer HHMM
        """
        ##open db connection
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        
        ##validate paramters
        if not verify_user_number_id(user_number_id, con):
            raise Call2RideError("ERR_UNKNOWN_USER_ID")
        
        ## check location_id
        if location_id == 0:
          location_id = self._get_default_location_id(user_number_id)

        lang = self._get_lang_by_user_number_id(user_number_id)

        ##translate start_key and end_key to ids start_point and end_point
        start_point, start_name = self._point_key_info(location_id, start_key)
        end_point, end_name = self._point_key_info(location_id, end_key)

        ##convert times    
        tzname = self.get_timezone_name(location_id)
        check_est = c2r_time_to_datetime(start_date, earliest_start_time, tzname, True)
        start_time = c2r_time_to_datetime(start_date, earliest_start_time, tzname)
        latest_start_time = c2r_time_to_datetime(start_date, latest_start_time, tzname, False, check_est)
        
        ##close all request before this
        self.close_request(user_number_id, start_point, end_point, False, start_date)
        
        ##insert into db
        cur.execute(self._INSERT_REQUEST_STATEMENT % (user_number_id,
           start_point,
           end_point,
           start_time,
           latest_start_time))
        con.commit()

        ## Find possible ride offers
        offer_text = ''
        cur.execute(self._POSSIBLE_RIDES_STATEMENT % (start_point, end_point))
        for roid, in cur.fetchall():
           cur2 = con.cursor()
           ride_res = self._get_ride_offer_info(roid)
           if ride_res != False:
             route_id, reverse, driver_name, driver_cell_phone_nr = ride_res
             st = self._get_steptime(route_id, start_point, end_point)
             if self._check_direction(route_id, reverse, start_point, end_point):
               sql2 = """SELECT start_time + time '%s'
                 FROM ride_offers WHERE 
                 start_time  + time '%s' >= TIMESTAMP WITH TIME ZONE '%s'
                 AND start_time  + time '%s' <= TIMESTAMP WITH TIME ZONE '%s' AND id = %d"""
               timematch = sql2 % (st, st, start_time, st, latest_start_time, roid)
               cur2.execute(timematch)
               amatch = cur2.fetchone()
               if amatch != None:
                 start_time_db = amatch[0]
                 point_starttime = self._dbtime_to_localtime(start_time_db, self.get_timezone_name(location_id))
                 offer_text += '%s, %s, %s\n' % (point_starttime.strftime(self._timeformat), driver_name, driver_cell_phone_nr)
             cur2.close()

        ##match transport
        cur.execute(self._REQ_PUBLIC_TRANSPORT_MATCH % (start_time, 
          latest_start_time,
          start_point, end_point))
        for deptime, means in cur.fetchall():
            offer_text += '%s, %s\n' % (deptime.strftime(self._timeformat), means)
        
        ##match taxi
        cur.execute(self._GET_TAXI_NUMBER_STATEMENT % location_id)
        
        res = cur.fetchone()
        if res is not None:
          offer_text += self._t(self._SMS_REQ_TAXI_TEXT, lang) % res[0]

        ##send sms
        sql_p = 'SELECT name from pickuppoint where id = %d'
        cur.execute(sql_p % start_point)
        res = cur.fetchone()
        p1name = res[0]

        cur.execute(sql_p % end_point)
        res = cur.fetchone()
        p2name = res[0]

        XSMS_FOUND_TEXT = """Your next rides from  %s to %s are:
        %s
        """

        XSMS_FOUND_TEXT = self._t(XSMS_FOUND_TEXT, lang)

        smstext = XSMS_FOUND_TEXT % (p1name.decode("utf-8"), p2name.decode("utf-8"), offer_text)
        xdriver_name, xdriver_cellphone = self._get_user_info(user_number_id)
        if send_sms==1:
          self._send_sms(xdriver_cellphone, smstext)
        
        cur.close()
        con.close()
        return smstext

    def close_request_route(self, user_number_id, location_id, route_key, reverse, throw=True, start_date=None):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      ##find route id
      sql = "select r.id from routes r, user_number un where key='%s' and r.user_id=un.user_id and un.id = %d and location_id=%d"
      cur.execute(sql % (route_key, user_number_id, location_id))
      res = cur.fetchone()
      if res == None:
        sql = "select id from routes where key='%s' and location_id=%d"
        cur.execute(sql % (route_key, location_id))
        res = cur.fetchone()
        if res == None:
          raise Call2RideError("ERR_UNKNOWN_ROUTE_ID")
      route_id = res[0]

      ##find point id
      rps = self.route_points_get(route_id)
      first = 1
      for (id, rid, pid, steptime, position) in rps:
        if first == 1:
          first_point = pid
        last_point = pid
        first = 0

      ## reverse
      if reverse:
        tmp = first_point
        first_point = last_point
        last_point = tmp

      return self.close_request(user_number_id, first_point, last_point, throw, start_date)

    def close_request_key(self, user_number_id, start_point_key, end_point_key, throw=True, start_date=None):
        location_id = self._get_default_location_id(user_number_id)
        sql = "select id from pickuppoint where location_id=%d and key='%s'"
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        cur.execute(sql % (location_id, start_point_key))
        res = cur.fetchone()
        sp = res[0]

        cur.execute(sql % (location_id, end_point_key))
        res = cur.fetchone()
        ep = res[0]
        self.close_request(user_number_id, sp, ep, throw, start_date)

    def close_request(self, user_number_id, start_point, end_point, throw=True, start_date=None):
        """
        will close all request according route_id and user_id
        @param throw: if True(defualt) a Call2RideError is thrown if there
        are no requests to close
        """
        ##open db connection
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        
        if not verify_user_number_id(user_number_id, con):
            raise Call2RideError("ERR_UNKNOWN_USER_ID")
        
        #if not verify_route_id(route_id, con):
        #    raise Call2RideError("ERR_UNKNOWN_ROUTE_ID")
        
        request_ids = get_active_request_ids(user_number_id, start_point, end_point, con, start_date)
        
        if request_ids != None:
            for request_id in request_ids:
                cur.execute(self._REQ_CLOSE_LOG_STATEMENT % request_id)
                cur.execute(self._REQ_CLOSE_STATEMENT % request_id)
        elif throw==True:
            raise Call2RideError("ERR_UNKNOWN_REQUEST")
             
        con.commit()
        cur.close()
        con.close()

    def close_offer_key(self, user_number_id, route_key, reverse, throw=True, start_date=None):
       location_id = self._get_default_location_id(user_number_id)
       sql = "select id from routes where location_id=%d and key='%s'"
       con = psycopg2.connect(self._dsn)
       cur = con.cursor()
       cur.execute(sql % (location_id, route_key))
       res = cur.fetchone()
       self.close_offer(user_number_id, res[0], reverse, throw, start_date)     

    def close_offer(self, user_number_id, route_id, reverse, throw=True, start_date=None):
        ##open db connection
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        
        if not verify_user_number_id(user_number_id, con):
            raise Call2RideError("ERR_UNKNOWN_USER_ID")
        
        if not verify_route_id(route_id, con):
            raise Call2RideError("ERR_UNKNOWN_ROUTE_ID")

        offer_ids = get_active_offer_ids(user_number_id, route_id, reverse, con, start_date)
        
        if offer_ids != None:
            for offer_id in offer_ids:
                cur.execute(self._OFFER_CLOSE_LOG_STATEMENT % (offer_id))
                cur.execute(self._OFFER_CLOSE_STATEMENT % (offer_id, reverse))
        elif throw==True:
             raise Call2RideError("ERR_UNKNOWN_OFFER")
        con.commit()
        cur.close()

    def _get_pickuppoint_name(self, point_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select name from pickuppoint where id = %d'
      cur.execute(sql % point_id)
      res = cur.fetchone()
      cur.close()
      con.close()
      if res != None:
        return res[0]
      return None
 
    def _t(self, text, lang):
      if lang == 'en' or lang == '' or lang is None:
        return text
      sql = "select translation from translation where key='%s' and lang='%s'"
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      sql = sql % (text, lang)
      sql = sql.replace('%', '%%')
      cur.execute(sql)
      res = cur.fetchone()
      cur.close()
      con.close()
      if res != None:
        return res[0].decode("utf-8")
              
      sql = "insert into translation (key, lang) values ('%s', '%s')"
      sql = sql % (text, lang)
      sql = sql.replace('%', '\%')
      self._do_sql(sql)
      return text
 
    def _get_lang_by_user_number_id(self, user_number_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select language from users u, user_number un where un.id = %d and u.id=un.user_id'
      cur.execute(sql % user_number_id)
      res = cur.fetchone()
      cur.close()
      con.close()
      if res != None:
        lang = res[0]
      if lang == '':
        lang = 'en'
      return lang
            
    def _get_user_info(self, user_number_id):
      logger.debug("_get_user_info method has user_id %d" % user_number_id)
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select name, number from users u, user_number un where un.id = %d and u.id=un.user_id'
      cur.execute(sql % user_number_id)
      res = cur.fetchone()
      cur.close()
      con.close()
      if res != None:
        return res
      return None

    def get_user_number(self, user_number_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select name, number from users u, user_number un where un.id = %d and u.id=un.user_id'
      cur.execute(sql % user_number_id)
      res = cur.fetchone()
      cur.close()
      con.close()
      if res != None:
        name, num = res
        return num
      return None

    def get_user_id_by_user_number_id(self, user_number_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      sql = 'select u.id, name from users u, user_number un where un.id = %d and u.id=un.user_id'
      cur.execute(sql % user_number_id)
      res = cur.fetchone()
      cur.close()
      con.close()
      if res != None:
        id, name = res
        return id
      return None

    def check_route_id(self, route_id):
        """
        Check if a route with id 'xx' is existing.
        @param route_id: int
        @returns: boolean
        """
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        
        result = verify_route_id(route_id, con)
        
        cur.close()
        con.close()
        return result

    def get_user_routes(self, user_id, location_id):
        """
        Get Routes per User
        """
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        cur2 = con.cursor()

        result = []
        sql = "select id, key, user_id from routes where (user_id is null or user_id = %d) and location_id = %d";
        cur.execute(sql % (user_id, location_id))
        for routes in cur.fetchall():
          sql = "select name, position from route_pickuppoint rp, pickuppoint p where rp.point_id=p.id and route_id = %d order by position asc limit 1"
          sql2 = "select name, position from route_pickuppoint rp, pickuppoint p where rp.point_id=p.id and route_id = %d order by position desc limit 1"
          cur2.execute(sql % routes[0])
          start,p1 = cur2.fetchone() 
          cur2.execute(sql2 % routes[0])
          ziel,p2  = cur2.fetchone()
          result.append((routes[1], routes[2], start, ziel))
        cur.close()
        cur2.close()
        con.close()
        return result

    def get_routes(self, side_id):
        """
        get all routes
        """
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        result = []
    
        cur.execute(self._GET_ROUTES_STATEMENT)
        for route_id in cur.fetchall():
            result.append(route_id[0])
        
        cur.close()
        con.close()
        return result

    def _get_steptime(self, route_id, start_point, end_point):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select steptime, position from route_pickuppoint where route_id=%d and point_id=%d'
      cur.execute(sql % (route_id, start_point))
      sp_st, sp_pos = cur.fetchone()
      cur.execute(sql % (route_id, end_point))
      ep_st, ep_pos = cur.fetchone()

      if sp_pos < ep_pos:
        return sp_st
      else:
        sql = 'select steptime from route_pickuppoint where route_id=%d order by position desc limit 1'
        cur.execute(sql % route_id)
        lp_st, = cur.fetchone()
        sql = """select time '%s' - time '%s'"""
        cur.execute(sql % (lp_st, sp_st))
        rev, = cur.fetchone()
        return rev

    def _check_direction(self, route_id, reverse, start_point, end_point):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select position from route_pickuppoint where route_id=%d and point_id=%d'
      cur.execute(sql % (route_id, start_point))
      sp_pos, = cur.fetchone()
      cur.execute(sql % (route_id, end_point))
      ep_pos, = cur.fetchone()
      cur.close()
      con.close()

      if reverse == 0:
        if sp_pos < ep_pos:
          return 1
        return 0
      else:
        if sp_pos > ep_pos:
          return 1
        return 0

    ##Translate start_key and end_key to ids start_point and end_point
    def _point_key_info(self, location_id, point_key):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = """select id, name from pickuppoint where location_id = %d and key = '%s'"""
      cur.execute(sql % (location_id, point_key))
      res = cur.fetchone()

      cur.close()
      con.close()

      return res

    ##Get Ride Offer Infos
    def _get_ride_offer_info(self, ride_offer_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = """ select ro.route_id, ro.reverse, u.name, un.number
         from ride_offers ro, users u, user_number un 
         where ro.id = %d and un.id=ro.user_number_id and un.user_id=u.id""" 
      cur.execute(sql % ride_offer_id)
      res = cur.fetchone()
      if res == None:
        return False
      route_id = res[0]
      name = res[2]
      cell_phone_nr = res[3]
      reverse = 0
      if res[1]:
        reverse = 1

      cur.close()
      con.close()

      return route_id, reverse, name, cell_phone_nr


    def get_timezone_name(self, location_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select timezone from location where id=%d'
      cur.execute(sql % location_id)
      timezonename, = cur.fetchone()
     
      cur.close()
      con.close()
      return timezonename

    def _get_point_key(self, pid):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select key from pickuppoint where id=%d'
      cur.execute(sql % pid)
      key, = cur.fetchone()
   
      cur.close()
      con.close()
      return key


    def _dbtime_to_localtime(self, atime, timezone):
      tz = pytz.timezone(timezone)
      localtime = atime.astimezone(tz)
      return localtime

    def get_user_number_id_from_email(self, email):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "select un.id from user_number un, users u where email='%s' and u.id=un.user_id and is_default=TRUE"
      cur.execute(sql % email)
      user_number_id, = cur.fetchone()

      cur.close()
      con.close()
      return user_number_id

    def _get_default_location_id(self, user_number_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "select default_location_id from user_number un, users u where un.id=%d and u.id=un.user_id"
      cur.execute(sql % user_number_id)
      default_location_id, = cur.fetchone()

      cur.close()
      con.close()
      return default_location_id

    def check_login(self, email, password):
      logger.debug('Check Login')	
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      hash = md5.new()
      hash.update('abc123' + password)  
      password_hash = hash.hexdigest()

      sql = "select id from users where email='%s' and password='%s' and is_active=TRUE"
      cur.execute(sql % (email, password_hash))

      uid, = cur.fetchone()
      cur.close()
      con.close()
      if uid != None:
        return uid
      return None


    def get_user_info_email(self, email):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "select id, name, email, company_id, default_location_id, group_id, is_active, language from users where email='%s'"
      cur.execute(sql % email)

      res = cur.fetchone()
      cur.close()
      con.close()
      if res != None:
        return res
      return None

    def get_user_info(self, id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "select id, name, email, company_id, default_location_id, group_id, is_active, language from users where id=%d"
      cur.execute(sql % id)

      res = cur.fetchone()
      cur.close()
      con.close()
      if res != None:
        return res
      return None

    def companies_get(self, id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "select id, name, smskey, zendeskid, logourl, email from company order by id asc"
      if (id>0):
        sql = "select id, name, smskey, zendeskid, logourl, email from company where id=%d order by id asc"
        sql = sql % id
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def locations_get(self):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select id, company_id, "Name", timezone, phone from location order by id asc'
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def pickuppoints_get(self):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select id, location_id, name, geo, key from pickuppoint order by id asc'
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def routes_get(self):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select id, origin, destination, status, key, location_id, user_id from routes order by id asc'
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def users_get(self):
      logger.debug('users_get-123-')
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select id, name, is_active, email, company_id, default_location_id, password, group_id from users order by id asc'
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def route_points_get(self, route_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'select id, route_id, point_id, to_char(steptime, \'HH24:MI:SS\'), position from route_pickuppoint where route_id = %d order by position asc'
      sql = sql % route_id
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def user_numbers_get(self, user_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = 'SELECT id,user_id,number,is_default, is_active FROM user_number where user_id = %d order by id'
      sql = sql % user_id
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def offers_get(self, user_id, past=1):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sqlwhere = ''
      if user_id > 0:
        sqlwhere = 'where user_number_id in (select id from user_number where user_id=%d)' % user_id

      if past == 0:
        if user_id == 0:
          sqlwhere = 'where '
        else:
          sqlwhere += ' and '
        sqlwhere += ' start_time >= current_timestamp'

      sql = 'SELECT id, user_number_id, route_id, to_char(start_time, \'YYYY-Mon-DD HH24:MI:SS\'), to_char(request_time, \'YYYY-Mon-DD HH24:MI:SS\'), status, reverse FROM ride_offers %s order by start_time desc'
      sql = sql % sqlwhere
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def departure_timetable_get(self, location_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      
      sql = 'SELECT o.route_id, to_char(o.start_time, \'HH24:MI\') AS time, p.name, u.name, n.number, u.email FROM (ride_offers o LEFT JOIN route_pickuppoint rp USING(route_id) LEFT JOIN pickuppoint p ON rp.point_id = p.id) LEFT JOIN user_number n ON o.user_number_id = n.id LEFT JOIN users u ON n.user_id = u.id WHERE o.start_time >= current_timestamp AND o.start_time < current_timestamp + INTERVAL \'12 hour\' AND o.status != \'closed\' AND o.reverse = true AND p.location_id = %d ORDER BY o.start_time ASC, o.route_id ASC, rp.position DESC' % location_id
      cur.execute(sql)
      
      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def offer_get(self, id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()	
      sql = 'SELECT id, user_number_id, route_id, to_char(start_time, \'YYYY-Mon-DD HH24:MI:SS\'), to_char(request_time, \'YYYY-Mon-DD HH24:MI:SS\'), status, reverse FROM ride_offers where id=%s'
      sql = sql % id
      cur.execute(sql)
      res = cur.fetchall()
      cur.close()
      con.close()
      return res[0]

    def requests_get(self, user_id, past=1):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sqlwhere = ''
      if user_id > 0:
        sqlwhere = 'where user_number_id in (select id from user_number where user_id=%d)' % user_id

      if past == 0:
        if user_id == 0:
          sqlwhere = 'where '
        else:
          sqlwhere += ' and '
        sqlwhere += ' latest_start_time >= current_timestamp'

      sql = 'SELECT id, user_number_id, start_point, end_point, to_char(earliest_start_time, \'YYYY-Mon-DD HH24:MI:SS\'), to_char(latest_start_time, \'YYYY-Mon-DD HH24:MI:SS\'), to_char(request_time, \'YYYY-Mon-DD HH24:MI:SS\'), status FROM ride_requests %s order by earliest_start_time desc'
      sql = sql % sqlwhere
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res

    def request_get(self, id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      sql = 'SELECT id, user_number_id, start_point, end_point, to_char(earliest_start_time, \'YYYY-Mon-DD HH24:MI:SS\'), to_char(latest_start_time, \'YYYY-Mon-DD HH24:MI:SS\'), to_char(request_time, \'YYYY-Mon-DD HH24:MI:SS\'), status FROM ride_requests where id = %s'
      sql = sql % id
      cur.execute(sql)

      res = cur.fetchall()
      cur.close()
      con.close()
      return res[0]

    def companies_insert(self, cname, smskey, zendeskid, logourl, email):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "insert into company (name, smskey, zendeskid, logourl, email) values ('%s', '%s', '%s', '%s', '%s')"
      sql = sql % (cname, smskey, zendeskid, logourl, email)
      cur.execute(sql)

      
      cur.close()
      con.commit()
      con.close()

    def locations_insert(self, lname, cid, timezone, phone):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "insert into location (\"Name\", company_id, timezone, phone) values ('%s', %d, '%s', '%s')"
      sql = sql % (lname, cid, timezone, phone)
      cur.execute(sql)
      
      cur.close()
      con.commit()
      con.close()

    def pickuppoints_insert(self, name, lid, geo, key):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "insert into pickuppoint (\"name\", location_id, geo, key) values ('%s', %d, '%s', '%s')"
      sql = sql % (name, lid, geo, key)
      cur.execute(sql)

      cur.close()
      con.commit()
      con.close()

    def routes_insert(self, origin, destination, status, key, lid, user_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "insert into routes (origin, destination, status, key, location_id, user_id) values ('%s', '%s', '%s', '%s', '%d', %d)"
      sql = sql % (origin, destination, status, key, lid, user_id)
      cur.execute(sql)

      sql = "SELECT currval('routes_seq')"
      cur.execute(sql)
      res = cur.fetchone()
      rid = res[0]

      cur.close()
      con.commit()
      con.close()

      return rid

    def companies_delete(self, cid):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "delete from company where id = %d"
      sql = sql % cid
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def locations_delete(self, lid):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "delete from location where id = %d"
      sql = sql % lid
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def pickuppoints_delete(self, id):
      self.delete_entry('pickuppoint', id)

    def routes_delete(self, id):
      self.delete_entry('routes', id)

    def delete_entry(self, table, id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "delete from %s where id = %d"
      sql = sql % (table, id)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def companies_update(self, cid, cname, smskey, zendeskid, logourl, email):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "update company set name='%s', smskey='%s', zendeskid='%s', logourl ='%s', email = '%s' where id = %d"
      sql = sql % (cname, smskey, zendeskid, logourl, email, cid)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def locations_update(self, lid, lname, cid, timezone, phone):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "update location set \"Name\"='%s', company_id=%d, timezone='%s', phone='%s' where id = %d"
      sql = sql % (lname, cid, timezone, phone, lid)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def pickuppoints_update(self, id, name, lid, geo, key):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "update pickuppoint set \"name\"='%s', location_id=%d, geo='%s', key='%s' where id = %d"
      sql = sql % (name, lid, geo, key, id)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def routes_update(self, id, origin, destination, status, key, lid, user_id):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "update routes set origin='%s', destination='%s', status='%s', key='%s', location_id=%d, user_id=%d where id = %d"
      sql = sql % (origin, destination, status, key, lid, user_id, id)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def userinfo_update(self, uid, name, email, cid, dlid, gid, is_active, language):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "update users set name='%s', email='%s', company_id=%d, default_location_id=%d, group_id=%d, is_active=%s, language='%s' where id = %d"
      isa = 'False'
      if is_active==1:
        isa = 'True'
      sql = sql % (name, email, cid, dlid, gid, isa, language, uid)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def userinfo_insert(self, name, email, cid, dlid, gid, is_active, number):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "insert into users (name, email, company_id, default_location_id, group_id, is_active) values ('%s', '%s', %d, %d, %d, %s)"
      isa = 'False'
      if is_active==1:
        isa = 'True'
      sql = sql % (name, email, cid, dlid, gid, isa)
      cur.execute(sql)

      sql = "SELECT currval('users_id_seq')"
      cur.execute(sql)
      res = cur.fetchone()
      uid = res[0]

      sql = "insert into user_number (user_id, number, is_default, is_active) values (%d, '%s', True, True)"
      sql = sql % (uid, number)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def user_delete(self, uid):
      sql = "delete from user_number where user_id=%d"
      sql = sql % uid
      self._do_sql(sql)
      sql = "delete from users where id=%d"
      sql = sql % uid
      self._do_sql(sql)  

    def routepoints_delete(self, rid):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "delete from route_pickuppoint where route_id = %d"
      sql = sql % (rid)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def routepoints_insert(self, rid, pid, st, pos):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()

      sql = "insert into route_pickuppoint (route_id, point_id, steptime, position) values (%d, %d, '%s', %d)"
      sql = sql % (rid, pid, st, pos)
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def user_update_default_location(self, uid, dlid):
      sql = "update users set default_location_id=%d where id=%d"
      sql = sql % (dlid, uid)
      self._do_sql(sql)

    def user_update_language(self, uid, language):
      sql = "update users set language='%s' where id=%d"
      sql = sql % (language, uid)
      self._do_sql(sql)

    def user_number_delete(self, unid):
      sql = "delete from user_number where id=%d"
      sql = sql % unid
      self._do_sql(sql)

    def user_number_default(self, uid, unid):
      sql = "update user_number set is_default = False where user_id=%d"
      sql = sql % uid
      self._do_sql(sql)
      sql = "update user_number set is_default = True where id=%d"
      sql = sql % unid
      self._do_sql(sql)

    def user_number_delete(self, unid):
      sql = "delete from user_number where id=%d"
      sql = sql % unid
      self._do_sql(sql)

    def user_number_add(self, uid, number, code):
      logger.debug('user_number_add')
      sql = "insert into user_number (user_id, number, is_default, validation_code, is_active) values (%d, '%s', False, '%s', False)"
      sql = sql % (uid, number, code)
      self._do_sql(sql)
      self._send_sms(number, 'Your validation code for Open CarPool is: %s' % code)

    def user_number_add_admin(self, uid, number):
      sql = "insert into user_number (user_id, number, is_default, is_active) values (%d, '%s', False,True)"
      sql = sql % (uid, number)
      self._do_sql(sql)

    def user_number_activate(self, unid, code):
      sql = "update user_number set is_active=True where validation_code='%s' and id=%d"
      sql = sql % (code, unid)
      self._do_sql(sql)

    def log_error(self, uid, code, message):
      sql = "insert into error_log (user_id, code, message, time) values (%d, '%s', '%s', current_timestamp)"
      sql = sql % (uid, code, message)
      self._do_sql(sql)

    def change_password(self, uid, new_password):
      hash = md5.new()
      hash.update('abc123' + new_password)
      password_hash = hash.hexdigest()

      sql = "update users set password='%s' where id=%d"
      sql = sql % (password_hash, uid)
      self._do_sql(sql)

    def _do_sql(self, sql):
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      cur.execute(sql)
      cur.close()
      con.commit()
      con.close()

    def close_single_offer(self, offer_id):
      sql = "update ride_offers set status = 'closed' where id = %d"
      sql = sql % offer_id
      self._do_sql(sql)

    def close_single_request(self, request_id):
      sql = "update ride_requests set status = 'closed' where id = %d"
      sql = sql % request_id
      self._do_sql(sql)

    def get_common_timezones(self):
      from pytz import common_timezones
      return common_timezones

    def GenPasswd(self):
      import string
      import random
      return "".join(random.sample(string.letters+string.digits, 10))
      
    def check_lost_password(self, key):
      sql = "SELECT id from users where lost_password='%s'"
      sql = sql % key
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      cur.execute(sql)
      uid, = cur.fetchone()
      cur.close()
      con.commit()
      con.close()
      if uid:
        sql = "update users set lost_password=Null where id=%d"
        sql = sql % uid
        self._do_sql(sql)
        return uid
      return 0


    def create_notification(self, user_id, message):
        ##open db connection
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        
        if not verify_user_number_id(user_id, con):
            raise Call2RideError("ERR_UNKNOWN_USER_ID")
        

        cur.execute(self._INSERT_NOTIFICATIONS_STATMENT % (user_id, message))
             
        con.commit()
        cur.close()
        con.close()
		
    def mark_notification_read(self, id):
        ##open db connection
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()

        cur.execute(self._UPDATE_NOTIFICATIONS_STATMENT % id)
             
        con.commit()
        cur.close()
        con.close()		

    def get_notification(self, user_id):
        ##open db connection
        con = psycopg2.connect(self._dsn)
        cur = con.cursor()
        
        if not verify_user_id(user_id, con):
            raise Call2RideError("ERR_UNKNOWN_USER_ID")
        

        cur.execute(self._GET_NOTIFICATIONS % user_id)
        
        res = cur.fetchall()
        
        con.commit()
        cur.close()
        con.close()	
        return res


    def lost_password(self, email, baseurl):
      sql = "SELECT email from users where email='%s'"
      sql = sql % email
      con = psycopg2.connect(self._dsn)
      cur = con.cursor()
      cur.execute(sql)
      res, = cur.fetchone()
      cur.close()
      con.commit()
      con.close()

      if res:
        key = self.GenPasswd()
        sql = "update users set lost_password='%s' where email='%s'"
        sql = sql % (key, email)
        self._do_sql(sql)

        import smtplib
        from email.mime.text import MIMEText
        message = "Please use this link and change your password: " + baseurl + "login.php?pwlost=%s"
        message = message % key
        msg = MIMEText(message)
        msg['Subject'] = 'Open CarPool: Lost Password'
        msg['From'] = 'support@opencarpool.org'
        msg['To'] = email
        s = smtplib.SMTP(self._config.get('mail', 'smtp'))
        s.ehlo()
        s.login(self._config.get('mail', 'user'), self._config.get('mail', 'password'))
        s.sendmail(msg['From'], [msg['To']], msg.as_string())
        s.quit()
        return 1
      return 0
      
      
    def accept_offer(self, user_number_id, offer_id, send_sms):
      
      send_sms = 1
      
      smstext = "Somebody accepted your offer!"
      if send_sms == 1:
        self._send_sms("+4917640382017", smstext)

      cur.close()
      con.close()
      return smstext
      
      
   
