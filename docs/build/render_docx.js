const fs = require('fs');
const d = require('docx');
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, Table, TableRow, TableCell,
  WidthType, ShadingType, BorderStyle, AlignmentType, PageBreak, TableOfContents,
  LevelFormat, PageOrientation, Footer, PageNumber, VerticalAlign
} = d;

const CONTENT = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
const OUT = process.argv[3];

// ---- page geometry: A4 portrait, 1440 dxa = 1 inch ----
const PAGE_W = 11906, MARGIN = 1080;
const TEXT_W = PAGE_W - MARGIN * 2;   // 9746

const C = {
  ink:    '1A1A1A',
  muted:  '5B6470',
  accent: '18506B',
  accent2:'0F6E52',
  rule:   'C9D2DA',
  headBg: 'E8EEF2',
  zebra:  'F6F8FA',
  noteBg: 'FFF8E6',
  noteBar:'C9922B',
  codeBg: 'F2F4F6',
};

const FONT = 'Calibri';
const MONO = 'Consolas';

function txt(s, o = {}) {
  return new TextRun({
    text: String(s == null ? '' : s),
    font: o.mono ? MONO : FONT,
    size: o.size || 20,          // half-points → 20 = 10pt
    bold: !!o.bold,
    italics: !!o.italics,
    color: o.color || C.ink,
  });
}

// Parse very light inline markup: **bold**, `code`
function rich(s, base = {}) {
  const out = [];
  // **bold** may wrap `code` and vice versa, so bold is resolved first and its
  // contents are re-scanned for code spans.
  const re = /(\*\*[\s\S]+?\*\*|`[^`]+`)/g;
  let last = 0, m;
  const push = (text, o) => { if (text) out.push(txt(text, o)); };
  const codeRun = (text, o) =>
    txt(text, { ...o, mono: true, color: o.bold ? o.color || C.accent : C.accent, size: o.size || 19 });

  while ((m = re.exec(s)) !== null) {
    push(s.slice(last, m.index), base);
    const tok = m[0];
    if (tok.startsWith('**')) {
      const inner = tok.slice(2, -2);
      const bold = { ...base, bold: true };
      let li = 0, cm;
      const cre = /`[^`]+`/g;
      while ((cm = cre.exec(inner)) !== null) {
        push(inner.slice(li, cm.index), bold);
        out.push(codeRun(cm[0].slice(1, -1), bold));
        li = cm.index + cm[0].length;
      }
      push(inner.slice(li), bold);
    } else {
      out.push(codeRun(tok.slice(1, -1), base));
    }
    last = m.index + tok.length;
  }
  push(s.slice(last), base);
  return out.length ? out : [txt(s, base)];
}

function P(s, o = {}) {
  return new Paragraph({
    children: rich(s, o),
    spacing: { before: o.before == null ? 0 : o.before, after: o.after == null ? 120 : o.after, line: 264 },
    alignment: o.align,
    indent: o.indent,
    border: o.border,
    shading: o.shading,
  });
}

function H(text, level) {
  const map = {
    1: { size: 30, color: C.accent,  before: 360, after: 160, hl: HeadingLevel.HEADING_1 },
    2: { size: 25, color: C.accent,  before: 300, after: 130, hl: HeadingLevel.HEADING_2 },
    3: { size: 22, color: C.accent2, before: 240, after: 110, hl: HeadingLevel.HEADING_3 },
    4: { size: 20, color: C.ink,     before: 200, after:  90, hl: HeadingLevel.HEADING_4 },
  }[level];
  return new Paragraph({
    heading: map.hl,
    spacing: { before: map.before, after: map.after },
    children: [txt(text, { bold: true, size: map.size, color: map.color })],
    border: level <= 2 ? { bottom: { style: BorderStyle.SINGLE, size: 6, space: 6, color: C.rule } } : undefined,
  });
}

function cell(content, o = {}) {
  const kids = Array.isArray(content) ? content : [
    new Paragraph({
      children: rich(String(content == null ? '' : content), { bold: o.bold, size: o.size || 19, color: o.color }),
      spacing: { before: 40, after: 40, line: 240 },
    })
  ];
  return new TableCell({
    children: kids,
    width: { size: o.w, type: WidthType.DXA },
    shading: o.bg ? { type: ShadingType.CLEAR, fill: o.bg, color: 'auto' } : undefined,
    margins: { top: 60, bottom: 60, left: 110, right: 110 },
    verticalAlign: VerticalAlign.TOP,
    columnSpan: o.span,
  });
}

function tbl(b) {
  const pct = b.cols;
  const total = pct.reduce((a, x) => a + x, 0);
  const widths = pct.map(p => Math.round(TEXT_W * p / total));
  widths[widths.length - 1] = TEXT_W - widths.slice(0, -1).reduce((a, x) => a + x, 0);

  const rows = [];
  if (b.header) {
    rows.push(new TableRow({
      tableHeader: true,
      children: b.header.map((h, i) => cell(h, { w: widths[i], bold: true, bg: C.headBg, color: C.accent })),
    }));
  }
  b.rows.forEach((r, ri) => {
    const bg = (ri % 2 === 1) ? C.zebra : undefined;
    rows.push(new TableRow({
      children: r.map((v, i) => cell(v, { w: widths[i], bg, bold: b.boldFirstCol && i === 0 })),
    }));
  });

  return new Table({
    columnWidths: widths,
    width: { size: TEXT_W, type: WidthType.DXA },
    rows,
    borders: {
      top:    { style: BorderStyle.SINGLE, size: 4, color: C.rule },
      bottom: { style: BorderStyle.SINGLE, size: 4, color: C.rule },
      left:   { style: BorderStyle.SINGLE, size: 4, color: C.rule },
      right:  { style: BorderStyle.SINGLE, size: 4, color: C.rule },
      insideHorizontal: { style: BorderStyle.SINGLE, size: 2, color: C.rule },
      insideVertical:   { style: BorderStyle.SINGLE, size: 2, color: C.rule },
    },
  });
}

function callout(b) {
  const label = b.label || 'Note';
  return new Table({
    columnWidths: [TEXT_W],
    width: { size: TEXT_W, type: WidthType.DXA },
    rows: [new TableRow({
      children: [new TableCell({
        width: { size: TEXT_W, type: WidthType.DXA },
        shading: { type: ShadingType.CLEAR, fill: b.bg || C.noteBg, color: 'auto' },
        margins: { top: 120, bottom: 120, left: 160, right: 160 },
        children: [
          new Paragraph({ children: [txt(label.toUpperCase(), { bold: true, size: 16, color: b.bar || C.noteBar })], spacing: { after: 60 } }),
          ...(Array.isArray(b.text) ? b.text : [b.text]).map(t =>
            new Paragraph({ children: rich(t, { size: 19 }), spacing: { after: 60, line: 250 } })),
        ],
      })],
    })],
    borders: {
      top: { style: BorderStyle.NONE }, bottom: { style: BorderStyle.NONE },
      right: { style: BorderStyle.NONE }, insideHorizontal: { style: BorderStyle.NONE },
      insideVertical: { style: BorderStyle.NONE },
      left: { style: BorderStyle.SINGLE, size: 18, color: b.bar || C.noteBar },
    },
  });
}

function codeBlock(b) {
  const lines = Array.isArray(b.text) ? b.text : String(b.text).split('\n');
  return new Table({
    columnWidths: [TEXT_W],
    width: { size: TEXT_W, type: WidthType.DXA },
    rows: [new TableRow({
      children: [new TableCell({
        width: { size: TEXT_W, type: WidthType.DXA },
        shading: { type: ShadingType.CLEAR, fill: C.codeBg, color: 'auto' },
        margins: { top: 110, bottom: 110, left: 150, right: 150 },
        children: lines.map(l => new Paragraph({
          children: [txt(l || ' ', { mono: true, size: 17, color: '243447' })],
          spacing: { after: 10, line: 230 },
        })),
      })],
    })],
    borders: {
      top: { style: BorderStyle.SINGLE, size: 2, color: C.rule },
      bottom: { style: BorderStyle.SINGLE, size: 2, color: C.rule },
      left: { style: BorderStyle.SINGLE, size: 2, color: C.rule },
      right: { style: BorderStyle.SINGLE, size: 2, color: C.rule },
      insideHorizontal: { style: BorderStyle.NONE }, insideVertical: { style: BorderStyle.NONE },
    },
  });
}

function spacer(h) { return new Paragraph({ children: [txt('')], spacing: { after: h || 100 } }); }

let stepInstance = 0;

function block(b) {
  switch (b.t) {
    case 'h1': return [H(b.text, 1)];
    case 'h2': return [H(b.text, 2)];
    case 'h3': return [H(b.text, 3)];
    case 'h4': return [H(b.text, 4)];
    case 'p':  return [P(b.text, { after: 140 })];
    case 'lead': return [P(b.text, { after: 170, size: 21, color: C.muted, italics: true })];
    case 'bullets': return b.items.map(i => new Paragraph({
      children: rich(i), numbering: { reference: 'bul', level: 0 },
      spacing: { after: 60, line: 252 },
    }));
    case 'steps': {
      const inst = ++stepInstance;          // each list gets its own instance so it restarts at 1
      return b.items.map(i => new Paragraph({
        children: rich(i), numbering: { reference: 'num', level: 0, instance: inst },
        spacing: { after: 70, line: 252 },
      }));
    }
    case 'table': return [tbl(b), spacer(140)];
    case 'note': return [callout(b), spacer(140)];
    case 'code': return [codeBlock(b), spacer(140)];
    case 'pagebreak': return [new Paragraph({ children: [new PageBreak()] })];
    case 'spacer': return [spacer(b.h)];
    default: throw new Error('unknown block ' + b.t);
  }
}

// ---------- cover ----------
function cover(meta) {
  const out = [];
  out.push(spacer(1700));
  out.push(new Paragraph({
    children: [txt(meta.eyebrow, { bold: true, size: 20, color: C.accent2 })],
    spacing: { after: 140 },
  }));
  out.push(new Paragraph({
    children: [txt(meta.title, { bold: true, size: 56, color: C.accent })],
    spacing: { after: 100 },
  }));
  out.push(new Paragraph({
    children: [txt(meta.subtitle, { size: 26, color: C.muted })],
    spacing: { after: 200 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 10, space: 12, color: C.rule } },
  }));
  (meta.coverPoints || []).forEach(p =>
    out.push(new Paragraph({ children: rich(p, { size: 20, color: C.muted }), spacing: { after: 80 } })));
  out.push(spacer(500));
  out.push(tbl({
    cols: [26, 74], boldFirstCol: true,
    rows: meta.coverFacts,
  }));
  out.push(new Paragraph({ children: [new PageBreak()] }));

  out.push(H('Contents', 1));
  out.push(new TableOfContents('Contents', { hyperlink: true, headingStyleRange: '1-3' }));
  out.push(new Paragraph({
    children: [txt('If the list above appears empty or stale, open it in Word and press Ctrl+A then F9 to refresh the field.', { size: 17, italics: true, color: C.muted })],
    spacing: { before: 200 },
  }));
  out.push(new Paragraph({ children: [new PageBreak()] }));
  return out;
}

const children = [...cover(CONTENT.meta)];
CONTENT.chapters.forEach((ch, idx) => {
  if (idx > 0) children.push(new Paragraph({ children: [new PageBreak()] }));
  ch.blocks.forEach(b => block(b).forEach(x => children.push(x)));
});

const doc = new Document({
  creator: 'Benzear ERP documentation',
  title: CONTENT.meta.title,
  description: CONTENT.meta.subtitle,
  numbering: {
    config: [
      { reference: 'bul', levels: [
        { level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 420, hanging: 240 } },
                   run: { font: FONT, size: 20, color: C.accent } } },
      ]},
      { reference: 'num', levels: [
        { level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 460, hanging: 280 } },
                   run: { font: FONT, size: 20, bold: true, color: C.accent } } },
      ]},
    ],
  },
  styles: { default: { document: { run: { font: FONT, size: 20, color: C.ink } } } },
  sections: [{
    properties: {
      page: {
        size: { width: PAGE_W, height: 16838, orientation: PageOrientation.PORTRAIT },
        margin: { top: MARGIN, bottom: MARGIN, left: MARGIN, right: MARGIN },
      },
    },
    footers: {
      default: new Footer({ children: [new Paragraph({
        alignment: AlignmentType.CENTER,
        border: { top: { style: BorderStyle.SINGLE, size: 4, space: 8, color: C.rule } },
        children: [
          txt(CONTENT.meta.footer + '   ·   ', { size: 16, color: C.muted }),
          new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: 16, color: C.muted }),
        ],
      })] }),
    },
    children,
  }],
});

Packer.toBuffer(doc).then(buf => { fs.writeFileSync(OUT, buf); console.log('wrote', OUT, buf.length, 'bytes'); });
