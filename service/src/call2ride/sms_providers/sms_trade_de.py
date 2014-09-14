# coding: utf8
import urllib
import warnings

##Codes
ERRORMESSAGES = {
10:"Empfängernummer nicht korrekt",
20:"Absenderkennung nicht korrekt",
30:"Nachrichtentext nicht korrekt",
31:"Messagetyp nicht korrekt",
40:"SMS Route nicht korrekt",
50:"Identifikation fehlgeschlagen",
60:"nicht genuegend Guthaben",
70:"Netz wird von Route nicht abgedeckt",
71:"Feature nicht über diese Route moeglich",
80:"Übergabe an SMS-C fehlgeschlagen",
90:"Versand nicht möglich",
100:"SMS wurde versendet"
}




class SmsGatewayError(Exception):
    """
    Error for SmsGateway smstrade.de, resolved with dict ERRORMESSAGES
    """
    def __init__(self, errorcode, message):
        Exception.__init__(self, errorcode, message)

class SmsGateway(object):
    """
    Specific smstrade.de implementation
    """
    def __init__(self, key):#
        """
        @param key: auth key for smstrade
        """
        self._key = key
    

    def send_sms(self, receiver, message):
        """
        @returns: messageid
        """
        params = {'key': self._key,
                  'to': receiver,
                  'route':'basic',
                  'ref':'car2ride',
                  #'debug':'1',
                  'concat_sms':'1',
                  'cost':'1',
                  'message_id':'1',
                  'from': 'OpenCarPool'
                  }
        
        if isinstance(message, unicode):
            params['charset'] = 'UTF-8'    
            params['message'] = message.encode('utf8')
        else:
            warnings.warn('Messages should be unicode objects!')
            params['message'] = message
        
        f_in = urllib.urlopen("https://gateway.smstrade.de",
                              urllib.urlencode(params))
        
        response = f_in.read()
        
        if len(response.splitlines()) != 3:
            return_code = response
        else:
            return_code, message_id, cost = response.splitlines()
        return_code = int(return_code)
        
        if return_code != 100:
            raise SmsGatewayError(return_code, ERRORMESSAGES[return_code])
        
        return {'message_id' :message_id, 'cost': float(cost)}


if __name__ == '__main__':
    smsp = SmsGateway('ENTER_YOUR_ID_HERE')
    smsp.send_sms('4917640382017', u'*'*1000)

    #smsp.send_sms('436508178203', u'*'*1000)
