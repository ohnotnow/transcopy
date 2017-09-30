# Transcopy

*NOTE* this is a work in progress and very much a 'scratch my own itch' project.

A very bare-bones web app which lets me copy files from my raspberry pi to my little PVR/media box.  My rpi runs transmission to download *entirely legitimate* torrents.  I then copy them to another pi which is running kodi (libreElec).  I was getting a bit fed up ssh'ing to the pi and running a script which copied files, so thought it would be easier to write a little web app so I could kick off the copying from my phone (or whatever) while I'm slumped on the sofa.

## What it does

It will show you a list of torrents (via the Transmission daemon) or a list of arbitrary files.  You can pick some of those and kick off a series of queued background jobs which will copy those files to another directory (eg, a samba share).  It does them one at a time as doing multiple files seems to make my poor rpi wifi fall over.

I'll write more once(if!) it all looks like it's going to work...

