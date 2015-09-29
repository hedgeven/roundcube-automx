# roundcube-automx
Automatically exported from code.google.com/p/roundcube-automx

This plugin is useful for setups in which multiple mail servers share a single roundcube installation.

Without this plugin, each user has to choose the mail server at login time. This is confusing for non-techie users. This plugin checks the MX record corresponding to the login email address and uses it as the mail server - making the mailserver selectbox obsolete.

The plugin has two prerequisites to work:

    The login for the imap accounts has to equal the email address of the account.
    The server hosting the imap account has to be the same as in the MX record of the domain. 

Works with a whitelist that can be freely defined in the config-file. Also hides the server-select-dropdown from the login-mask. 
