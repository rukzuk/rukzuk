#!/usr/local/bin/python2.7
# encoding: utf-8
'''
modulupdate -- rukzuk module exchange script

modulupdate is a script to replace or hotfix rukzuk modules

@copyright:  2014 rukzuk AG. All rights reserved.

@license:    GPLv2

@contact:    info@rukzuk.com
'''

import sys
import os
import re
import json
import tarfile

import logging
logger = logging.getLogger(__name__)

logger.setLevel(logging.DEBUG)
ch = logging.StreamHandler()
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
ch.setFormatter(formatter)
logger.addHandler(ch)

from argparse import ArgumentParser

__all__ = []


MANIFEST_FILENAME = 'moduleManifest.json'


def loadModuleReplacements(hotfix_directory):
    """
    Returns a list of tuples.

    The tuple includes (in this order):
     - the module id
     - a regex to check if the current module version should be replaces
     - the path to a tar file with the new module
    """
    hotfixes = [entry for entry in os.listdir(hotfix_directory)
                if os.path.isdir(os.path.join(hotfix_directory, entry))]
    replacements = []
    for hotfix in hotfixes:
        newtar_filepath = os.path.join(hotfix_directory, hotfix,
                                       'hotfix.tar.gz')
        mete_filepath = os.path.join(hotfix_directory, hotfix,
                                        'hotfix.json')
        if os.path.isfile(mete_filepath):
            meta_data = json.load(open(mete_filepath, 'r'))
        replacements.append((meta_data['moduleId'], meta_data['version'],
                             newtar_filepath))
    return replacements


def patchModule(module_path, patchfile):
    logger.info("patching %s with %s" % (module_path, patchfile))
    tf = tarfile.open(patchfile)
    tf.extractall(module_path)
    tf.close()


def findModules(search_directory):
    modules = []
    for dirpath, _dirnames, filenames in os.walk(search_directory):
        try:
            if MANIFEST_FILENAME in filenames \
            and os.path.basename(dirpath) == 'module':
                logger.debug("Found module %s" % dirpath)
                module_path = os.path.dirname(dirpath)
                module_id = os.path.basename(module_path)
                manifest = json.load(open(os.path.join(dirpath,
                                                       MANIFEST_FILENAME)))
                module_version = manifest['version']
                modules.append((module_path,
                                module_id,
                                module_version))
        except Exception:
            logging.exception("Defect module detected")
            pass
    return modules


def updateModules(hotfix_directory, search_directory):
    replacements = loadModuleReplacements(hotfix_directory)
    modules = findModules(search_directory)
    for module_path, module_id, module_version in modules:
        for replacement_id, version_rexex, patchfile in replacements:
            if module_id == replacement_id:
                if re.match(version_rexex, module_version):
                    patchModule(module_path, patchfile)


def validate_args(args):
    """
    Validates the given command line arguments.

    Raises CLIError when the arguments are invalid .
    """
    if not os.path.isdir(args.HotfixDirectory):
        raise Exception('Invalid hot fix directory')
    if not os.path.isdir(args.SearchDirectory):
        raise Exception('Invalid search directory')
    if os.path.realpath(args.HotfixDirectory) == os.path.realpath(
                                                    args.SearchDirectory):
        raise Exception('Invalid search directory')


def main(argv=None):  # IGNORE:C0111
    '''Command line options.'''

    if argv is None:
        argv = sys.argv
    else:
        sys.argv.extend(argv)

    # Setup argument parser
    parser = ArgumentParser()
    parser.add_argument('HotfixDirectory')
    parser.add_argument('SearchDirectory')

    # Process arguments
    args = parser.parse_args()
    validate_args(args)
    updateModules(args.HotfixDirectory, args.SearchDirectory)

    return 0


if __name__ == "__main__":
    sys.exit(main())
