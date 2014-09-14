# coding: utf8
"""
Dummy SMS Module for testing purposes
"""

import logging
LOGGER = logging.getLogger(__name__)


class SmsGateway(object):
    """
    Specific smstrade.de implementation
    """
    def __init__(self, *args):
        """
        @param key: auth key for smstrade
        """
    
    def send_sms(self, receiver, message):
        LOGGER.info('Receiver: "%s", Message: "%s"',
            (receiver, message))
        return {}
