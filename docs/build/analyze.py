import re,sys,os,json
def analyze(path):
    if not os.path.exists(path): return None
    t=open(path,encoding='utf8',errors='replace').read()
    def u(x): 
        seen=[];[seen.append(i) for i in x if i not in seen];return seen
    ins=u(re.findall(r"INSERT\s+(?:IGNORE\s+)?INTO\s+`?(\w+)`?", t, re.I))
    upd=u(re.findall(r"UPDATE\s+`?(\w+)`?\s+SET", t, re.I))
    dele=u(re.findall(r"DELETE\s+FROM\s+`?(\w+)`?", t, re.I))
    sel=u(re.findall(r"(?:FROM|JOIN)\s+`?(\w+)`?", t, re.I))
    sel=[s for s in sel if not s.lower() in ('select','dual')]
    inc=u(re.findall(r"(?:include|require)(?:_once)?\s*\(?\s*['\"]([^'\"]+)", t))
    links=u(re.findall(r"(?:href|action|window\.location(?:\.href)?\s*=|location\.href\s*=)\s*=?\s*[\"']([a-zA-Z0-9_\-]+\.php)", t))
    ajax=u(re.findall(r"url\s*:\s*[\"']([^\"']+\.php)", t))
    return dict(file=os.path.basename(path), lines=t.count("\n")+1, kb=round(len(t)/1024,1),
                insert=ins, update=upd, delete=dele, select=sel, include=inc, links=links, ajax=ajax)
if __name__=="__main__":
    for f in sys.argv[1:]:
        r=analyze(f)
        if r is None: print(f"## {f}  -- FILE NOT FOUND"); continue
        print(f"\n## {r['file']}  ({r['lines']} lines, {r['kb']} KB)")
        for k in ('insert','update','delete'):
            if r[k]: print(f"  {k.upper():7}: {', '.join(r[k])}")
        if r['select']: print(f"  READS  : {', '.join(r['select'][:40])}")
        if r['include']: print(f"  INCLUDE: {', '.join(r['include'])}")
        if r['ajax']: print(f"  AJAX   : {', '.join(r['ajax'][:15])}")
        if r['links']: print(f"  LINKS  : {', '.join(r['links'][:25])}")
