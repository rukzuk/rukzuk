# If it exists, it usually defines a default account.
# This allows msmtp to be used like /usr/sbin/sendmail.
account default

# The SMTP smarthost.
host ##HOST##
auth on
user ##USER##
password ##PASSWORD##

# Construct envelope-from addresses of the form "user@oursite.example".
auto_from on
maildomain ##FROM##

# Use TLS.
tls on
tls_certcheck off
#tls_trust_file /etc/ssl/certs/ca-certificates.crt

# Syslog logging with facility LOG_MAIL instead of the default LOG_USER.
syslog LOG_MAIL

