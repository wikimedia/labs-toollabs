AC_INIT([toollabs], m4_esyscmd_s([sed -ne '1s/^toollabs (\(.\+\)) .*$/\1/p;' debian/changelog]), [cloud-admin@lists.wikimedia.org])
AC_CONFIG_AUX_DIR([build-aux])

# If parsing the current version from debian/changelog did not work
# (debian/changelog mangled, wrong pattern, sed not available, etc.),
# autoreconf usually aborts with an obscure error message ("should be
# called with package and version arguments").  In case it would
# nevertheless continue, PACKAGE_VERSION would be empty.  So check
# that it is set, otherwise abort.
if test -z "$PACKAGE_VERSION"; then
  AC_MSG_ERROR([PACKAGE_VERSION was not properly set.])
fi

AM_INIT_AUTOMAKE([foreign no-dist silent-rules -Wall -Werror])
AC_PROG_INSTALL
AC_PROG_LN_S
AC_PROG_CXX

# Parse the release date from debian/changelog.
AC_SUBST([TOOLLABS_RELEASEDATE], ["$(date --date="$(sed -ne '1,/^ -- .*  /s/^ -- .*  //p;' debian/changelog)" +%Y-%m-%d)"])

# We parse $TOOLLABS_RELEASEDATE and $PACKAGE_VERSION from
# debian/changelog, so we need to depend config.status on it.
AC_SUBST([CONFIG_STATUS_DEPENDENCIES], ['$(top_srcdir)/debian/changelog'])

AC_CONFIG_FILES([Makefile
                 jobutils/Makefile
                 jobutils/bin/Makefile
                 jobutils/man/Makefile
                 jobutils/man/job.1
                 jobutils/man/jstop.1
                 jobutils/man/jsub.1
                 misctools/Makefile
                 misctools/become.1
                 misctools/sql.1
                 src/Makefile
                 src/take.1])

AC_OUTPUT
