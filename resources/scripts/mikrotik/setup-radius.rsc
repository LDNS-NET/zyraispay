# MikroTik RADIUS Configuration Script for Billyetu
# This script configures the router to use the Billyetu API for user authentication

# Disable the default hotspot server if it exists
/ip hotspot disable [find]

# Create a new RADIUS client
/radius
add address=your-api-domain.com secret=your-radius-secret service=hotspot,login,wireless

# Configure the hotspot server with RADIUS authentication
/ip hotspot profile
add name=billyetu-hotspot hotspot-address=0.0.0.0 html-directory=hotspot login-by=http-chap,http-pap,mac-cookie use-radius=yes

# Set up the hotspot server interface
/ip hotspot
add name=billyetu interface=bridge-local profile=billyetu-hotspot disabled=no

# Configure DNS settings
/ip dns set allow-remote-requests=yes servers=8.8.8.8,8.8.4.4

# Add firewall rules to allow hotspot traffic
/ip firewall nat
add chain=srcnat action=masquerade out-interface=WAN

/ip firewall mangle
add chain=prerouting action=mark-connection new-connection-mark=hotspot_conn \
    connection-state=new in-interface=bridge-local
add chain=prerouting action=mark-connection new-connection-mark=hotspot_conn \
    connection-mark=hotspot_conn in-interface=bridge-local
add chain=prerouting action=mark-connection new-connection-mark=hotspot_conn \
    connection-state=established,related in-interface=bridge-local

# Log the configuration
:log info "Billyetu Hotspot configuration completed"
