@echo off
call ../lib/configs/windows.bat
%AR_PHP%php -q export %1 %2 %3 %4 %5
