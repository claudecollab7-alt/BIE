# -*- coding: utf-8 -*-
import json
import part0, part1, part2, part3, part4, part5, part6, part7, part8
import part9, part10, part11

meta = {
    "eyebrow": "BIE ERP  ·  FUNCTIONAL AND TECHNICAL DOCUMENTATION",
    "title": "BIE ERP",
    "subtitle": "Complete walkthrough, menu by menu — forms, tables, flows and where every change shows up",
    "coverPoints": [
        "Complete · all 11 menus · all 63 screens · Parts 0 to 11",
        "Written for both the people who use BIE and the people who maintain it.",
    ],
    "coverFacts": [
        ["System", "BIE ERP — PHP + MySQL, 105 tables, 11 menus, 63 screens, 3 branches"],
        ["This document", "Complete — Parts 0 to 11, covering all 63 screens across all 11 menus"],
        ["Schema source", "db/bie.sql (the current dump in the repository)"],
        ["Depth", "Purpose · tables used · driving fields · full flow · every table write · where it reflects"],
        ["Scope note", "Live menu-reachable files only. Legacy and duplicate variants are excluded"],
        ["Read first", "Part 0 — and section 0.3 in particular, which explains how branches work"],
    ],
    "footer": "BIE ERP documentation",
}

content = {"meta": meta, "chapters": [
    part0.chapter(), part1.chapter(), part2.chapter(), part3.chapter(),
    part4.chapter(), part5.chapter(), part6.chapter(), part7.chapter(),
    part8.chapter(), part9.chapter(), part10.chapter(), part11.chapter(),
]}

def walk(o, path="root"):
    if isinstance(o, dict):
        for k, v in o.items(): walk(v, path + "." + k)
    elif isinstance(o, list):
        for i, v in enumerate(o): walk(v, "%s[%d]" % (path, i))
    elif o is None:
        if not path.endswith(".header"):
            raise SystemExit("unexpected None at " + path)
walk(content)

json.dump(content, open("content.json", "w"), ensure_ascii=False, indent=1)
n = sum(len(c["blocks"]) for c in content["chapters"])
print("chapters:", len(content["chapters"]), " blocks:", n)
