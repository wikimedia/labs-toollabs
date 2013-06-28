#!/bin/bash

# This script dumps arguments to dummy.args, STDIN to dummy.in,
# dummy.out to STDOUT, dummy.err to STDERR and exits with the status
# contained in dummy.exitcode.

# Abort on any error.
set -e

if [ $# -gt 0 ]; then
  while [ $# -gt 1 ]; do
    printf '%q ' "$1"
    shift
  done
  printf '%q\n' "$1"
fi > dummy.args
cat > dummy.in
cat dummy.out
cat dummy.err 1>&2

read EXITCODE < dummy.exitcode

exit $EXITCODE
