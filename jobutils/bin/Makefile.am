bin_SCRIPTS = job jstop jsub

install-exec-hook:
	cd $(DESTDIR)$(bindir) && \
	  $(LN_S) jsub jstart && \
	  $(LN_S) jsub qcronsub
