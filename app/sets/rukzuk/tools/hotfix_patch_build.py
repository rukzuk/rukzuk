#!/usr/bin/env python
# Requirements:
#   Python >= 2.6
#   pip install sh
#   sudo apt-get install patchutils nodejs
#   sudo npm install -g grunt

import sys
import os
import shutil
import sh
from sh import git
from sh import diff
from sh import lsdiff
from sh import lsdiff
from sh import grunt
from sh import npm
import re
import json
#import tarfile
from sh import tar

#### Config
olddir="old"
newdir="new"
destdir="patchset"
gitRepo = "ssh://www-data@data.rukzuk.net/srv/git-repos/bare-modules"

#####
if len(sys.argv) < 2:
  print sys.argv[0], "<old-gitish> <new-gitish>"
  sys.exit(1);

oldver=sys.argv[1]
newver=sys.argv[2]

print 'Create patchset of', gitRepo,'diff for tags: ', '"'+oldver+'"', '"'+newver+'"'

olddirPath=os.path.abspath(os.path.join(os.curdir, olddir))
newdirPath=os.path.abspath(os.path.join(os.curdir, newdir))
destdirPath=os.path.abspath(os.path.join(os.curdir, destdir))

# clone repo 2 times
if not os.path.isdir(olddirPath):
  print "clone source repo"
  git.clone(gitRepo, olddir)
else:
  print "source: use checkout in folder", olddir

if not os.path.isdir(newdirPath):
  print "clone target repo"
  git.clone(gitRepo, newdir)
else:
  print "target: use checkout in folder", newdir

# checkout correct version
print "checkout ", oldver
git.checkout(oldver, _cwd=olddirPath)
print "checkout ", newver
git.checkout(newver, _cwd=newdirPath)

# diff
changedList = lsdiff( diff("-Nura", "--exclude=.git*", '.', olddirPath, _ok_code=[0, 1, 2, 3], _cwd=newdirPath) )

print "Changed Files:"
print changedList

# grunt build in new folder
npm.install(_cwd=newdirPath)
grunt.min(_cwd=newdirPath)

changedModuleIds = []
for f in changedList:
  filePath = f.strip(' \t\n\r')
  fileParts = filePath.split('/')
  modulName = fileParts[1]
  destDirs = fileParts[2:]

  if not 'MODUL' in modulName: continue

  moduleId = modulName.split('_')[-1] # TODO: this is only valid for old modules!
  changedModuleIds.append(moduleId)

  #print filePath
  #print moduleId
  #print destDirs

  fullDestdir = os.path.join(destdirPath, moduleId, '/'.join(destDirs[:-1]))

  #print fullDestdir
  try:
    os.makedirs(fullDestdir)
  except OSError:
    pass

  shutil.copy(os.path.join(newdirPath, filePath[2:]), os.path.join(fullDestdir, destDirs[-1]))


print "create hotfix.tar.gz / hotfix.json"

changedModuleIds = set(changedModuleIds)
for moduleId in changedModuleIds:
  #tar = tarfile.open(os.path.join(destdirPath, moduleId + 'patch', 'hotfix.tar.gz'))
  modulePath = os.path.join(destdirPath, moduleId)
  print modulePath
  tar('--remove-files', '-czf', 'hotfix.tar.gz', [os.path.relpath(i, modulePath) for i in sh.glob(modulePath + '/*') ], _cwd=modulePath) #i.replace(modulePath + '/', '')
  meta = {"moduleId": moduleId, "version": re.escape(oldver[1:])}
  json.dump(meta, open(os.path.join(modulePath, 'hotfix.json'), 'w+'))


print "Changed Modules ", len(changedModuleIds)

## TODO: remove old dirs
## TODO: create patch config file


