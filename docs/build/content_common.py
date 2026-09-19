# -*- coding: utf-8 -*-
"""Shared block helpers for the Benzear documentation generator."""

def h1(t): return {"t": "h1", "text": t}
def h2(t): return {"t": "h2", "text": t}
def h3(t): return {"t": "h3", "text": t}
def h4(t): return {"t": "h4", "text": t}
def p(t):  return {"t": "p", "text": t}
def lead(t): return {"t": "lead", "text": t}
def bul(items): return {"t": "bullets", "items": items}
def steps(items): return {"t": "steps", "items": items}
def code(t): return {"t": "code", "text": t}
def pb(): return {"t": "pagebreak"}
def sp(h=120): return {"t": "spacer", "h": h}

def table(cols, header, rows, bold_first=False):
    return {"t": "table", "cols": cols, "header": header, "rows": rows, "boldFirstCol": bold_first}

def note(text, label="Note", bar="C9922B", bg="FFF8E6"):
    return {"t": "note", "text": text if isinstance(text, list) else [text],
            "label": label, "bar": bar, "bg": bg}

def warn(text):
    return note(text, label="Watch out", bar="B3452F", bg="FDF0EC")

def tip(text):
    return note(text, label="Good to know", bar="0F6E52", bg="EBF6F1")

# ---------- the standard "one form" section ----------

def form(num, title, at_glance, what, tables, fields, flow, writes, reflects,
         notes=None, extra=None):
    """Render one menu form as a consistent run of blocks."""
    b = [h3("%s  %s" % (num, title))]

    b.append(table([22, 78], None,
                   [[k, v] for k, v in at_glance], bold_first=True))

    b.append(h4("What this screen is for"))
    for para in (what if isinstance(what, list) else [what]):
        b.append(p(para))

    b.append(h4("Tables it uses"))
    b.append(table([26, 14, 60], ["Table", "How used", "What it holds / why it is touched"], tables))

    if fields:
        b.append(h4("The fields that matter"))
        b.append(table([26, 74], ["Field (column)", "Meaning, and what it drives"], fields))

    b.append(h4("How it flows, step by step"))
    b.append(steps(flow))

    b.append(h4("What changes in the tables"))
    b.append(table([16, 26, 58], ["Action", "Table", "Exactly what happens"], writes))

    b.append(h4("Where the result shows up"))
    b.append(table([34, 66], ["Shows up in", "What you see there"], reflects))

    for n in (notes or []):
        b.append(n)
    for e in (extra or []):
        b.append(e)
    return b


# ---------- the SHORT "one form" section (phase 2 onward) ----------

def form2(num, title, glance, what, tables, flow, writes, reflects, notes=None, fields=None,
          extra=None):
    """Shorter, plainer screen write-up."""
    b = [h3("%s  %s" % (num, title))]
    b.append(table([20, 80], None, [[k, v] for k, v in glance], bold_first=True))

    b.append(h4("What it does"))
    for para in (what if isinstance(what, list) else [what]):
        b.append(p(para))

    b.append(h4("Tables"))
    b.append(table([30, 70], ["Table", "Used for"], tables))

    if fields:
        b.append(h4("Fields worth knowing"))
        b.append(table([28, 72], ["Field", "What it means"], fields))

    b.append(h4("Flow"))
    b.append(steps(flow))

    b.append(h4("What changes"))
    b.append(table([18, 28, 54], ["Action", "Table", "What happens"], writes))

    b.append(h4("Shows up in"))
    b.append(bul(reflects))

    for e in (extra or []):
        b.append(e)
    for n in (notes or []):
        b.append(n)
    return b


def mini(num, title, file, what, tbl_name, fields, used_by):
    """A very short block for a simple lookup screen."""
    return [
        h4("%s  %s" % (num, title)),
        table([20, 80], None, [
            ["What it does", what],
            ["File", file],
            ["Table", tbl_name],
            ["Fields", fields],
            ["Used by", used_by],
        ], bold_first=True),
    ]
