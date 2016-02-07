// Copyright © 2013 Marc-André Pelletier <mpelletier@wikimedia.org>
//
// Permission to use, copy, modify, and/or distribute this software for any
// purpose with or without fee is hereby granted, provided that the above
// copyright notice and this permission notice appear in all copies.
//
// THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
// WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
// MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
// ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
// WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
// ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
// OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.

#include <dirent.h>
#include <errno.h>
#include <fcntl.h>
#include <getopt.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <unistd.h>

bool refuse(const char* file, const char* why)
{
    fprintf(stderr, "%s: %s\n", file, why);
    return false;
}

bool error(const char* file)
{
    return refuse(file, strerror(errno));
}

// This is just convenience for autoclosing files and prevent fd leaks
struct FD {
    int     fd;

            FD(int f): fd(f)        { };
            ~FD()               { if(fd >= 0) close(fd); };
            operator int (void) const   { return fd; };
};

gid_t   groups[256];
int ngroups;
bool cwdchanged = false;

bool takeover(const char* path, bool trustpath)
{
    // We open the files and use the file descriptors exclusively
    // to avoid trickery and race conditions
    FD file = open(path, O_RDONLY|O_NOFOLLOW);

    if(file < 0) {
        if(errno == ELOOP)
            return refuse(path, "will not follow or touch symlinks");
        return error(path);
    }

    struct stat sfile;

    if(fstat(file, &sfile))
        return error(path);

    if(!trustpath) {
        char    dirname[strlen(path)+1];

        strcpy(dirname, path);

        if(path[0] && path[strlen(path)-1]=='/')
            dirname[strlen(path)-1] = 0;

        if(char* slash = strrchr(dirname, '/'))
            *slash = 0;
        else
            strcpy(dirname, ".");

        int dir  = open(dirname, O_RDONLY|O_NOFOLLOW);

        if(dir < 0) {
            if(errno == ELOOP)
                return refuse(dirname, "will not follow or touch symlinks");
            return error(dirname);
        }

        struct stat sdir;
        if(fstat(dir, &sdir)) {
            close(dir);
            return error(dirname);
        }

        if(!S_ISDIR(sdir.st_mode)) {
            close(dir);
            return refuse(dirname, "containing directory doesn't appear to be... a directory?");
        }

        if(sdir.st_uid != getuid()) {
            close(dir);
            return refuse(path, "you must own the containing directory");
        }

        // Here, we are being extremely paranoid, and check that the file is actually in the directory.
        if(sdir.st_dev != sfile.st_dev) {
            close(dir);
            return refuse(path, "the file must be on the same filesystem as its directory");
        }

        bool found = false;
        if(DIR* df = fdopendir(dir)) {
            while(dirent* d = readdir(df)) {
                if(sfile.st_ino == d->d_ino) {
                    found = true;
                    break;
                }
            }
            closedir(df);
        } else
            return error(dirname);

        if(!found)
            return refuse(path, "the file isn't in its directory?");
    }

    if(!S_ISDIR(sfile.st_mode) && sfile.st_nlink != 1)
        return refuse(path, "will only touch files with no hardlinks");

    // check that the group matches
    bool found = false;
    for(int i=0; i<ngroups; i++)
        if(sfile.st_gid == groups[i]) {
            found = true;
            break;
        }
    if(!found)
        return refuse(path, "You need to share a group with the file");

    // everything checks out; do it.  Do it!  Doooo it!
    if(fchown(file, getuid(), -1))
        return error(path);

    // recursion for fun and profit
    if(S_ISDIR(sfile.st_mode)) {
        if(DIR* df = fdopendir(file)) {
            bool    ok = true;

            while(dirent* d = readdir(df)) {
                if(d->d_name[0]=='.') {
                    if(d->d_name[1]==0)
                        continue;
                    if(d->d_name[1]=='.' && d->d_name[2]==0)
                        continue;
                }
                fchdir(file);
                cwdchanged = true;
                ok &= takeover(d->d_name, true);
            }
            closedir(df);

            return ok;
        } else
            return error(path);
    }

    return true;
}

void usage(void) {
    puts("usage: take [-h] [--version] FILE [FILE ...]\n"       \
         "\n"                                                   \
         "Assume ownership of files and directories\n"          \
         "\n"                                                   \
         "positional arguments:\n"                              \
         "  FILE        file or directory to take over\n"       \
         "\n"                                                   \
         "optional arguments:\n"                                \
         "  -h, --help  show this help message and exit\n"      \
         "  --version   show program's version number and exit");
}

int main(int argc, char** argv)
{
    int opt;
    struct option options[] = {
        { "help",    no_argument, NULL, 'h' },
        { "version", no_argument, NULL, 'V' },
        { NULL,      0,           NULL, 0 },
    };

    while ((opt = getopt_long(argc, argv, "h", options, NULL)) != -1) {
        switch (opt) {
            case 'h':
                usage();
                return 0;

            case 'V':
                puts("take (Wikimedia Labs Tools misctools) " PACKAGE_VERSION "\n"                         \
                     "Copyright (C) 2013 Marc-André Pelletier\n"                                           \
                     "License ISC: <https://www.isc.org/downloads/software-support-policy/isc-license/>\n" \
                     "This is free software: you are free to change and redistribute it.\n"                \
                     "There is NO WARRANTY, to the extent permitted by law.");
                return 0;

            default:
                // An error message has already been printed by
                // getopt_long().
                fputs("Run take --help for usage.\n", stderr);
                return 1;
        }
    }

    if (optind >= argc) {
        fprintf(stderr, "No files to take were provided.\nRun take --help for usage.\n");
        return 1;
    }

    ngroups = getgroups(sizeof(groups)/sizeof(groups[0]), groups);

    int cwd = open(".", O_RDONLY);
    bool ok = true;
    for(int arg=optind; arg<argc; arg++) {
        if (cwdchanged)
            fchdir(cwd);
        ok &= takeover(argv[arg], false);
    }
    close(cwd);
    return ok? 0: 1;
}

