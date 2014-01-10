AC_INIT([toollabs], m4_esyscmd_s([sed -ne '1s/^toollabs (\(.\+\)) .*$/\1/p;' debian/changelog]), [mpelletier@wikimedia.org])
AC_CONFIG_AUX_DIR([build-aux])
AC_CONFIG_TESTDIR([tests])

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

AC_CONFIG_FILES([Makefile
                 jobutils/Makefile
                 jobutils/bin/Makefile
                 jobutils/man/Makefile
                 misctools/Makefile
                 src/Makefile
                 tests/Makefile])

AC_OUTPUT
