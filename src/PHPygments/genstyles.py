#!/usr/bin/python
# -*- coding: utf-8 -*-

#################### argumments
CSSCLASS_BASE = "highlighted-source"

#call pygments and return styled code
from pygments.formatters import HtmlFormatter

from pygments.styles import get_all_styles

styles = list(get_all_styles())
allstyles = ""

for style in styles:
    generated = (HtmlFormatter(style=style).get_style_defs('.' + CSSCLASS_BASE + '.' + style))
    file = open('./styles/' + style + '.css', 'w+')
    file.write(generated)
    file.close()

    allstyles = allstyles + "\n\n/* styles for " + style + " */\n" + generated


#finnaly we save all styles in a single file.
allstylesfile = open('./styles/all.css', 'w+')
allstylesfile.write(allstyles)
allstylesfile.close()

