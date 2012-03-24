all:
	rsync -avuz --exclude=.git* --exclude=web/results* . thomas@lolut.humanoidz.org:~/buildroot/

