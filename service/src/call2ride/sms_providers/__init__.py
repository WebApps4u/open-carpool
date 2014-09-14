"""
module for general exceptions and definitons...
"""

class SmsGatewayError(Exception):
    """
    Error for SmsGateway smstrade.de, resolved with dict ERRORMESSAGES
    """
    def __init__(self, *args):
        Exception.__init__(self, *args)
