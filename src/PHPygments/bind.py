#!/usr/bin/python
# -*- coding: utf-8 -*-

#################### argumments
import argparse

parser = argparse.ArgumentParser(description="noop")
parser.add_argument('--sourcefile')
parser.add_argument('--style')
parser.add_argument('--lang')
parser.add_argument('--linenumbers')
args = parser.parse_args()

CSSCLASS_BASE = "highlighted-source"

#call pygments and return styled code
from pygments import highlight
from pygments.lexers import get_lexer_by_name
from pygments.formatters import HtmlFormatter

#prevent errors
args.linenumbers = args.linenumbers if args.linenumbers == "table" or args.linenumbers == "inline" else False
cssclass = CSSCLASS_BASE + ' ' + args.style + ' ' + args.lang.replace("+", "-")
cssclass = cssclass + " " if args.linenumbers is not False else cssclass

code = open(args.sourcefile).read()

formatter = HtmlFormatter(cssclass=cssclass, linenos=args.linenumbers)
lexer = get_lexer_by_name(args.lang)

print(highlight(code, lexer, formatter).encode('utf8'))