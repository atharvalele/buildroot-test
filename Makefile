all:
	rsync -avuz --exclude=.git* --exclude=web/results* --exclude=web/stats* --exclude=web/toolchains* . thomas@lolut.humanoidz.org:~/buildroot/

