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

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <errno.h>
#include <fcntl.h>
#include <dirent.h>
#include <sys/types.h>
#include <sys/stat.h>

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
	int		fd;

			FD(int f): fd(f)		{ };
			~FD()				{ if(fd >= 0) close(fd); };
			operator int (void) const	{ return fd; };
};

gid_t	groups[256];
int	ngroups;
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

	struct stat	sfile;

	if(fstat(file, &sfile))
		return error(path);

	if(!trustpath) {
		char	dirname[strlen(path)+1];

		strcpy(dirname, path);

		if(path[0] && path[strlen(path)-1]=='/')
			dirname[strlen(path)-1] = 0;

		if(char* slash = strrchr(dirname, '/'))
			*slash = 0;
		else
			strcpy(dirname, ".");

		FD dir  = open(dirname, O_RDONLY|O_NOFOLLOW);

		if(dir < 0) {
			if(errno == ELOOP)
				return refuse(dirname, "will not follow or touch symlinks");
			return error(dirname);
		}

		struct stat	sdir;
		if(fstat(dir, &sdir))
			return error(dirname);

		if(!S_ISDIR(sdir.st_mode))
			return refuse(dirname, "containing directory doesn't appear to be... a directory?");
		if(sdir.st_uid != getuid())
			return refuse(path, "you must own the containing directory");

		// Here, we are being extremely paranoid, and check that the file is actually in the directory.
		if(sdir.st_dev != sfile.st_dev)
			return refuse(path, "the file must be on the same filesystem as its directory");

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
			bool	ok = true;

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
		} else
			return error(path);
	}

	return true;
}


int main(int argc, char** argv)
{
	ngroups = getgroups(sizeof(groups)/sizeof(groups[0]), groups);

	int cwd = open(".", O_RDONLY);
	bool ok = true;
	for(int arg=1; arg<argc; arg++) {
		if (cwdchanged)
			fchdir(cwd);
		ok &= takeover(argv[arg], false);
    }
	close(cwd);
	return ok? 0: 1;
}

