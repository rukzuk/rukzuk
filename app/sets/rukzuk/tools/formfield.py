#!/usr/bin/env python2

import os
import json
import collections
import io

import logging
logger = logging.getLogger(__name__)


def fix_first_headline(filename):
    try:
        with open(filename) as f:
            data = json.load(f, object_pairs_hook=collections.OrderedDict)
        data['form'][0]['name'] = "{\"de\":\"Eigenschaften\",\"en\":\"Properties\"}"
        with io.open(filename, 'w', encoding='utf8') as json_file:
            data = json.dumps(data, separators=(',', ': '), indent=4, ensure_ascii=False)
            try:
                json_file.write(data)
            except TypeError:
                # Decode data to Unicode first
                json_file.write(data.decode('utf8'))
    except IndexError:
        print "Error while: %s" % filename
    except KeyError:
        print "Error while: %s" % filename


def form_files_generator():
    logger.info('Searching module form.json files')
    for root, _dirs, files in os.walk('.'):  # TODO: Make this sys arg 1
        for name in files:
            if name == 'form.json':
                yield os.path.join(root, name)


if __name__ == '__main__':
    for filename in form_files_generator():
        fix_first_headline(filename)
