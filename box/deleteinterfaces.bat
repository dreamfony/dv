for /l %%x in (1, 1, 100) do (
   echo %%x;
   vboxmanage hostonlyif remove "VirtualBox Host-Only Ethernet Adapter #%%x"
)