import re,sys
data=open("db/bie.sql",encoding='utf8',errors='replace').read()
for t in sys.argv[1:]:
    m=re.search(r"CREATE TABLE `%s` \((.*?)\n\) ENGINE"%re.escape(t), data, re.S)
    if not m: print(f"\n### {t}: NOT FOUND"); continue
    cols=re.findall(r"^\s+`(\w+)` ([^,\n]+?)(?:,|$)", m.group(1), re.M)
    print(f"\n### {t}  ({len(cols)} cols)")
    for c,d in cols: print(f"   {c:<34} {d[:70]}")
