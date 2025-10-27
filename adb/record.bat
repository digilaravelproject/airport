
@echo off
goto :Code
==============================================================================
RECORD.BAT

Usage:
Only the first parameter is used and should be an integer>=0
%1>0 will record video for that many number of seconds (mp4)
%1==0 will take a screenshot (png)

The file name created will be named based on the time the file is pulled
from the device.

==============================================================================
==============================================================================
:Code

adb devices
if %1==0 (
    adb shell screencap -p /sdcard/screen.png
    adb pull /sdcard/screen.png
    adb shell rm /sdcard/screen.png
    for /f "tokens=1-5 delims=:" %%d in ("%time%") do rename "screen.png" %%d-%%e-%%f.png
) else (
    adb shell screenrecord --time-limit %1 --bit-rate 6000000 /sdcard/demo.mp4
    adb pull /sdcard/demo.mp4
    adb shell rm /sdcard/demo.mp4
    for /f "tokens=1-5 delims=:" %%d in ("%time%") do rename "demo.mp4" %%d-%%e-%%f.mp4
)

goto :End



=============================================================================
==============================================================================

:End