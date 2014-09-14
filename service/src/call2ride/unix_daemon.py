"""
Helper for starting a python script as daemon
"""

###########################################################################
# configure these paths:
#LOGFILE = '/var/log/pydaemon.log'
#PIDFILE = '/var/run/pydaemon.pid'

# and let USERPROG be the main function of your project
#+import mymain
#USERPROG = mymain.main
###########################################################################

#based on Juergen Hermanns http://aspn.activestate.com/ASPN/Cookbook/Python/Recipe/66012
import sys
import os
import logging

logger = logging.getLogger(__name__)
helplog = None

class Log:
    """file like for writes with auto flush after each write
    to ensure that everything is logged, even during an
    unexpected exit."""
    def __init__(self, f):
        self.f = f
    def write(self, s):
        self.f.write(s)
        self.f.flush()



def start_as_deamon(fun, pidfile, logfile=Log(open('/home/opencarpool/dev_webservice.log', 'w')), workingdir=os.getcwd()):
    """
    start the fun function as deamon process
    """
    ##TODO static filename!
    #logfile = Log(open(logfilename, 'w'));
    
    #logging.basicConfig(filename='/home/call2ride/jsonrpc_webservice.log',
    #                    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
    #                    level=logging.DEBUG,)


    # do the UNIX double-fork magic, see Stevens' "Advanced
    # Programming in the UNIX Environment" for details (ISBN 0201563177)
    def main(fun, logfile):
        #change to data directory if needed
        os.chdir(workingdir)
        #redirect outputs to a logfile
        sys.stdout = sys.stderr = logfile
        #ensure the that the daemon runs a normal user
        #os.setegid(103)     #set group first "pydaemon"
        #os.seteuid(103)     #set user "pydaemon"
        #start the user program here:
        while True:
            try:
                logger.debug('Started main function')
                fun()
            except:
                logger.exception('killed due exception')
        
    try:
        pid = os.fork()
        if pid > 0:
            # exit first parent
            sys.exit(0)
    except OSError, e:
        print >>sys.stderr, "fork #1 failed: %d (%s)" % (e.errno, e.strerror)
        sys.exit(1)

    # decouple from parent environment
    os.chdir("/")   #don't prevent unmounting....
    os.setsid()
    os.umask(0)

    # do second fork
    try:
        pid = os.fork()
        if pid > 0:
            # exit from second parent, print eventual PID before
            print "Userid", os.geteuid()
            print "Daemon PID %d" % pid
            open(pidfile,'w').write("%d"%pid)
            sys.exit(0)
    except OSError, e:
        print >>sys.stderr, "fork #2 failed: %d (%s)" % (e.errno, e.strerror)
        sys.exit(1)

    # start the daemon main loop
    main(fun, logfile)
