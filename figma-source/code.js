// =====================================================================
//  StayTrack — Editable Figma UI Kit & Screen Recreation
//  Generated from the live StayTrack website (http://127.0.0.1:8000)
//  Run: Figma > Plugins > StayTrack UI Source > "Generate StayTrack Design"
//  Every node is a plain Figma object (frames, rectangles, texts) so the
//  entire design stays fully editable.
// =====================================================================

const css = {
  pageW: 1440,
  sidebarW: 256,
  topbarH: 72,
  pad: 32,
  contentW: 1440 - 256 - 64, // 1120
  font: { family: "Figtree", reg: "Regular", med: "Medium", semi: "SemiBold", bold: "Bold", exbold: "ExtraBold" },
  col: {
    ink: hex2rgb("#0f172a"),
    slate100: hex2rgb("#f1f5f9"),
    slate200: hex2rgb("#e2e8f0"),
    slate300: hex2rgb("#cbd5e1"),
    slate400: hex2rgb("#94a3b8"),
    slate500: hex2rgb("#64748b"),
    slate600: hex2rgb("#475569"),
    slate700: hex2rgb("#334155"),
    slate800: hex2rgb("#1e293b"),
    slate900: hex2rgb("#0f172a"),
    white: hex2rgb("#ffffff"),
    page: hex2rgb("#f8fafc"),
    blue: hex2rgb("#2563eb"),
    blue100: hex2rgb("#dbeafe"),
    blue50: hex2rgb("#eff6ff"),
    blue700: hex2rgb("#1d4ed8"),
    green: hex2rgb("#059669"),
    green100: hex2rgb("#d1fae5"),
    green50: hex2rgb("#ecfdf5"),
    green700: hex2rgb("#047857"),
    amber: hex2rgb("#d97706"),
    amber100: hex2rgb("#fde68a"),
    amber50: hex2rgb("#fffbeb"),
    amber700: hex2rgb("#b45309"),
    red: hex2rgb("#dc2626"),
    red50: hex2rgb("#fef2f2"),
    red700: hex2rgb("#b91c1c"),
    shadow: { type: "DROP_SHADOW", color: { r: 0.09, g: 0.09, b: 0.16, a: 0.08 }, offset: { x: 0, y: 4 }, radius: 12, spread: 0, visible: true, blendMode: "NORMAL" },
    shadowSm: { type: "DROP_SHADOW", color: { r: 0.09, g: 0.09, b: 0.16, a: 0.05 }, offset: { x: 0, y: 1 }, radius: 3, spread: 0, visible: true, blendMode: "NORMAL" },
  },
};

function hex2rgb(hex) {
  const n = hex.replace("#", "");
  return { r: parseInt(n.slice(0, 2), 16) / 255, g: parseInt(n.slice(2, 4), 16) / 255, b: parseInt(n.slice(4, 6), 16) / 255 };
}

// ---------------------------------------------------------------------
//  Figtree font loading (with ExtraBold fallback to Bold)
// ---------------------------------------------------------------------
const fontsLoaded = {};
let fontSet = null;

function font(style) {
  return { family: css.font.family, style: style };
}

async function loadFonts() {
  const wanted = ["Regular", "Medium", "SemiBold", "Bold", "ExtraBold"];
  const available = [];
  for (const w of wanted) {
    try {
      await figma.loadFontAsync(font(w));
      available.push(w);
    } catch (e) { /* skip unavailable */ }
  }
  fontSet = available;
}

function pickFont(weight) {
  // weight: 400 | 500 | 600 | 700 | 800
  if (weight >= 800 && fontSet.includes("ExtraBold")) return font("ExtraBold");
  if (weight >= 700) return font("Bold");
  if (weight >= 600) return font("SemiBold");
  if (weight >= 500) return font("Medium");
  return font("Regular");
}

// ---------------------------------------------------------------------
//  Primitives
// ---------------------------------------------------------------------
function frame(name, x, y, w, h, fill, radius, opts) {
  const f = figma.createFrame();
  f.name = name;
  f.x = Math.round(x);
  f.y = Math.round(y);
  f.resize(w, h);
  if (fill) {
    f.fills = [{ type: "SOLID", color: fill }];
  } else {
    f.fills = [];
  }
  if (radius !== undefined) {
    f.cornerRadius = radius;
    f.clipsContent = true;
  }
  if (opts) {
    if (opts.stroke) f.strokes = [{ type: "SOLID", color: opts.stroke }];
    if (opts.strokeWeight) f.strokeWeight = opts.strokeWeight;
    if (opts.shadow) f.effects = [css.col.shadow];
    if (opts.shadowSm) f.effects = [css.col.shadowSm];
    if (opts.borderStyle) f.strokeAlignStyle = opts.borderStyle;
    if (opts.clip === false) f.clipsContent = false;
  }
  return f;
}

function rect(parent, x, y, w, h, fill, radius, opts) {
  const r = figma.createRectangle();
  r.name = opts && opts.name ? opts.name : "Rect";
  r.x = Math.round(x);
  r.y = Math.round(y);
  r.resize(w, h);
  r.fills = fill ? [{ type: "SOLID", color: fill }] : [];
  if (radius) r.cornerRadius = radius;
  if (opts) {
    if (opts.stroke) r.strokes = [{ type: "SOLID", color: opts.stroke }];
    if (opts.strokeWeight) r.strokeWeight = opts.strokeWeight;
    if (opts.shadow) r.effects = [css.col.shadow];
  }
  parent.appendChild(r);
  return r;
}

function ellipse(parent, x, y, w, h, fill, opts) {
  const e = figma.createEllipse();
  e.name = opts && opts.name ? opts.name : "Ellipse";
  e.x = Math.round(x);
  e.y = Math.round(y);
  e.resize(w, h);
  e.fills = fill ? [{ type: "SOLID", color: fill }] : [];
  if (opts) {
    if (opts.stroke) e.strokes = [{ type: "SOLID", color: opts.stroke }];
    if (opts.strokeWeight) e.strokeWeight = opts.strokeWeight;
  }
  parent.appendChild(e);
  return e;
}

function line(parent, x1, y1, x2, y2, color, w) {
  const d = Math.hypot(x2 - x1, y2 - y1);
  const a = (Math.atan2(y2 - y1, x2 - x1) * 180) / Math.PI;
  const l = figma.createRectangle();
  l.name = "Line";
  l.resize(d, w);
  l.x = x1;
  l.y = y1 - w / 2;
  l.rotation = a;
  l.fills = [{ type: "SOLID", color: color }];
  parent.appendChild(l);
  return l;
}

function text(parent, x, y, w, value, size, weight, color, opts) {
  const t = figma.createText();
  t.name = opts && opts.name ? opts.name : "Text";
  t.fontName = pickFont(weight);
  t.fontSize = size;
  t.characters = value;
  t.fills = [{ type: "SOLID", color: color }];
  if (w) {
    // Fixed width: auto-grow the height so text wraps and aligns within w.
    t.textAutoResize = "HEIGHT";
    t.resize(Math.round(w), Math.round(size * 1.4));
  } else {
    t.textAutoResize = "WIDTH_AND_HEIGHT";
  }
  if (opts && opts.auto === false) t.textAutoResize = "NONE";
  t.x = Math.round(x);
  t.y = Math.round(y);
  if (opts) {
    if (opts.lineHeight) t.lineHeight = { value: opts.lineHeight, unit: "PIXELS" };
    if (opts.ls) t.letterSpacing = { value: opts.ls, unit: "PIXELS" };
    if (opts.align) t.textAlignHorizontal = opts.align;
  }
  parent.appendChild(t);
  return t;
}

// ---------------------------------------------------------------------
//  Support: placeholder text chunks for tables (fixed cell text)
// ---------------------------------------------------------------------
function tcell(parent, x, y, w, value, size, weight, color, opts) {
  const t = text(parent, x, y, w, value, size, weight, color, opts || {});
  t.textAutoResize = "HEIGHT";
  t.resize(w, t.height);
  if (opts && opts.align) t.textAlignHorizontal = opts.align;
  return t;
}

function ellipseArc(parent, cx, cy, r, thickness, startDeg, endDeg, color) {
  const e = figma.createEllipse();
  e.name = "Arc";
  e.resize(r * 2, r * 2);
  e.x = cx - r;
  e.y = cy - r;
  e.fills = [];
  e.strokes = [{ type: "SOLID", color: color }];
  e.strokeWeight = thickness;
  const a = startDeg * (Math.PI / 180);
  const b = endDeg * (Math.PI / 180);
  try {
    e.arcData = { startingAngle: a, endingAngle: b, innerRadius: 0 };
  } catch (err) { /* ignore */ }
  parent.appendChild(e);
  return e;
}

// ---------------------------------------------------------------------
//  Icon library — every icon is drawn from primitive shapes so results
//  stay fully editable. Coordinate space is 0..20 scaled by `s/20`.
// ---------------------------------------------------------------------
function icon(parent, x, y, name, color, s, sw) {
  s = s || 20;
  const S = s / 20;
  const W = (sw || 2) * S;
  const L = (x1, y1, x2, y2, w) => line(parent, x + x1 * S, y + y1 * S, x + x2 * S, y + y2 * S, color, w || W);
  const R = (rx, ry, rw, rh, fill, rad, c2, w2, nm) => rect(parent, x + rx * S, y + ry * S, rw * S, rh * S, fill, (rad || 0) * S, c2 && { stroke: c2, strokeWeight: (w2 || W) }, nm && { name: nm });
  const E = (cx, cy, rw, rh, fill, c2, w2, nm) => ellipse(parent, x + (cx - rw / 2) * S, y + (cy - rh / 2) * S, rw * S, rh * S, fill, c2 && { stroke: c2, strokeWeight: (w2 || W), name: nm }, nm && { name: nm });

  switch (name) {
    case "home":
      L(2, 10, 10, 3.2); L(18, 10, 10, 3.2);
      L(3.2, 10, 3.2, 17); L(16.8, 10, 16.8, 17); L(3.2, 17, 16.8, 17);
      L(8, 17, 8, 12); L(12, 17, 12, 12); L(8, 12, 12, 12);
      break;
    case "grid":
      R(3, 3, 6, 6, color, 1.6); R(11, 3, 6, 6, color, 1.6);
      R(3, 11, 6, 6, color, 1.6); R(11, 11, 6, 6, color, 1.6);
      break;
    case "building":
      R(4, 3, 12, 14, null, 1.5, color, 1.8);
      R(7, 6, 2.4, 3, null, 0.4, color, 1.4); R(11, 6, 2.4, 3, null, 0.4, color, 1.4);
      R(7, 11, 2.4, 3, null, 0.4, color, 1.4); R(11, 11, 2.4, 3, null, 0.4, color, 1.4);
      L(8, 17, 8, 14); L(12, 17, 12, 14); L(8, 14, 12, 14);
      break;
    case "users":
      E(6.5, 5.5, 3.4, 3.4, null, color, 1.7); R(3.4, 10, 6.2, 4.6, null, 2.2, color, 1.7);
      E(14, 4.5, 2.6, 2.6, null, color, 1.5); R(11.6, 8.4, 4.8, 3.8, null, 2, color, 1.5);
      break;
    case "file":
      R(4, 2.4, 12, 15.4, null, 2, color, 1.8);
      L(6.6, 6, 13.6, 6); L(6.6, 9.4, 13.6, 9.4); L(6.6, 12.8, 11, 12.8);
      break;
    case "receipt":
      R(4, 2.6, 12, 15, null, 1.4, color, 1.8);
      L(6.8, 6, 13.2, 6); L(6.8, 9.4, 13.2, 9.4); L(6.8, 12.8, 10.4, 12.8);
      L(14.5, 14.5, 13.4, 14.5); L(13.4, 15.4, 12.3, 15.4); L(12.3, 14.5, 11.2, 14.5);
      break;
    case "card":
      R(2, 4.4, 16, 11.4, null, 1.8, color, 1.8);
      L(6, 4.4, 6, 15.8); L(10, 8, 15, 8); L(10, 11, 13, 11);
      break;
    case "user":
      E(6, 5.8, 4, 4, null, color, 1.8);
      E(4.6, 11.4, 2.8, 4.6, null, color, 1.8);
      break;
    case "bell":
      E(4.2, 9.4, 8.4, 8.4, null, color, 1.8);
      E(10, 6, 3.4, 3.4, null, color, 1.8);
      E(7.4, 4.4, 5.2, 5.2, null, color, 1.6);
      L(7, 13.4, 13, 13.4);
      break;
    case "menu":
      L(3, 6, 17, 6); L(3, 10, 17, 10); L(3, 14, 17, 14);
      break;
    case "x":
      L(5, 5, 15, 15); L(15, 5, 5, 15);
      break;
    case "plus":
      L(10, 3, 10, 17); L(3, 10, 17, 10);
      break;
    case "arrow":
      L(3, 10, 15, 10); L(10, 5, 15, 10); L(10, 15, 15, 10);
      break;
    case "chev":
      L(7.2, 4.4, 13.4, 10.2); L(7.2, 15.6, 13.4, 10.2);
      break;
    case "back":
      L(12.8, 4.4, 6.6, 10.2); L(12.8, 15.6, 6.6, 10.2);
      break;
    case "search":
      E(5.6, 7.2, 4.8, 4.8, null, color, 1.8);
      L(9.4, 11, 15.6, 17.2);
      break;
    case "eye":
      E(3.2, 7.2, 13.6, 5.6, null, color, 1.6);
      E(10, 10, 3, 1.9, null, color, 1.6);
      break;
    case "edit":
      L(4.2, 16, 13.4, 6.8); L(13.4, 6.8, 16.6, 4); L(14.6, 3, 17.2, 5.6);
      L(4.2, 16, 3.2, 16.8);
      break;
    case "trash":
      R(6, 4.2, 8, 14, null, 1.6, color, 1.7);
      L(5, 6.2, 15, 6.2); L(8.2, 3, 11.8, 3);
      L(8, 9, 8, 13.4); L(12, 9, 12, 13.4);
      break;
    case "alert":
      L(10, 3.2, 17.2, 16); L(17.2, 16, 2.8, 16); L(2.8, 16, 10, 3.2);
      L(10, 8, 10, 12.2); E(10, 13.6, 1.6, 1.6, color);
      break;
    case "check":
      L(3.4, 11.2, 8.2, 15.6); L(16.6, 4.8, 8.2, 15.6);
      break;
    case "wallet":
      R(2, 5.4, 16, 10.2, null, 2, color, 1.7);
      L(4.6, 9.6, 15.4, 9.6); L(4.6, 13.2, 15.4, 13.2);
      break;
    case "wrench":
      E(4.6, 5.6, 4.4, 4.4, null, color, 1.7);
      L(8, 8.8, 16.4, 17.2); L(12.6, 15.4, 17.4, 17.4);
      break;
    case "out":
      R(8, 3, 8.4, 14, null, 2, color, 1.8);
      L(3, 10, 13, 10); L(3, 10, 7.4, 6); L(3, 10, 7.4, 14);
      break;
    case "download":
      L(4, 13.6, 4, 17); L(16, 13.6, 16, 17); L(4, 17, 16, 17);
      L(10, 3.6, 10, 13.4); L(6.4, 9.6, 10, 13.4); L(13.6, 9.6, 10, 13.4);
      break;
    case "home-filled":
      R(3, 9.4, 14, 7.6, color, 1.6); R(8, 12.6, 4, 4.4, color, 1);
      break;
  }
}

// ---------------------------------------------------------------------
//  Brand mark
// ---------------------------------------------------------------------
function brandMark(parent, x, y, size, darkBg) {
  const s = size || 36;
  const bg = frame("brand-mark", x, y, s, s, css.col.white, s * 0.28);
  icon(bg, s * 0.22, s * 0.22, "home", css.col.slate900, s * 0.56, 2);
  return bg;
}

// ---------------------------------------------------------------------
//  Typographic atoms
// ---------------------------------------------------------------------
function eyebrow(parent, x, y, value, color, size) {
  return text(parent, x, y, null, value, size || 12, 600, color || css.col.slate600, { name: "eyebrow", ls: 1, lineHeight: Math.round((size || 12) * 1.4) });
}

// ---------------------------------------------------------------------
//  Buttons
// ---------------------------------------------------------------------
function button(parent, x, y, w, label, style, iconName, h) {
  h = h || 44;
  style = style || "primary";
  const bg = style === "primary" ? css.col.ink : style === "danger" ? css.col.red : css.col.white;
  const f = frame("button " + style, x, y, w, h, bg, 10);
  if (style === "secondary" || style === "ghost") {
    f.strokes = [{ type: "SOLID", color: css.col.slate200 }];
    f.strokeWeight = 1;
  }
  if (style === "ghost") f.fills = [];
  const txCol = style === "primary" || style === "danger" ? css.col.white : css.col.slate700;
  if (iconName) {
    const labelW = Math.round(label.length * 8.2);
    const contentW = 16 + 8 + labelW;
    const left = Math.max(Math.round((w - contentW) / 2), 0);
    icon(f, left, (h - 16) / 2, iconName, txCol, 16, 1.9);
    const t = text(f, left + 28, (h - 18) / 2, null, label, 14, 600, txCol, { name: "label", lineHeight: 18 });
  } else {
    const t = text(f, 0, (h - 18) / 2, w, label, 14, 600, txCol, { name: "label", align: "CENTER", lineHeight: 18 });
  }
  return f;
}

function iconButton(parent, x, y, size, iconName, tint, radius, strokeC) {
  size = size || 36;
  const f = frame("icon-button", x, y, size, size, tint || null, radius || 8);
  if (strokeC) { f.strokes = [{ type: "SOLID", color: strokeC }]; f.strokeWeight = 1; }
  const is = size - 16;
  icon(f, (size - is) / 2, (size - is) / 2, iconName, css.col.slate600, is, 2);
  return f;
}

function textLink(parent, x, y, label, alignRight) {
  if (alignRight) {
    const t = text(parent, x, y, null, label, 13, 600, css.col.ink, { name: "text-link", lineHeight: 18 });
    const iw = 14;
    const tw = label.length * 6.9;
    icon(parent, x + tw + 4, y + 2, "arrow", css.col.ink, 14, 2);
    return t;
  }
  const t = text(parent, x, y, null, label, 13, 600, css.col.ink, { name: "text-link", lineHeight: 18 });
  const tw = t.width;
  icon(parent, x + tw + 4, y + 2, "arrow", css.col.ink, 14, 2);
  return t;
}

// ---------------------------------------------------------------------
//  Badges & tags
// ---------------------------------------------------------------------
function badge(parent, x, y, label, tone, large) {
  tone = tone || "neutral";
  const map = {
    success: { bg: css.col.green50, tx: css.col.green700 },
    info: { bg: css.col.blue50, tx: css.col.blue700 },
    warning: { bg: css.col.amber50, tx: css.col.amber700 },
    danger: { bg: css.col.red50, tx: css.col.red700 },
    neutral: { bg: css.col.slate100, tx: css.col.slate600 },
  };
  const m = map[tone];
  const h = large ? 30 : 24;
  const w = label.length * (large ? 7.6 : 6.4) + (large ? 24 : 18);
  const f = frame("badge " + tone, x, y, w, h, m.bg, h / 2);
  const t = text(f, 0, (h - 16) / 2, w, label, large ? 13 : 12, 600, m.tx, { align: "CENTER" });
  t.textAutoResize = "HEIGHT";
  t.resize(w, t.height);
  return f;
}

function tag(parent, x, y, label, tone) {
  const map = {
    green: { bg: css.col.green100, tx: hex2rgb("#065f46"), dot: css.col.green },
    blue: { bg: css.col.blue100, tx: hex2rgb("#1e40af"), dot: css.col.blue },
    slate: { bg: css.col.slate200, tx: css.col.slate700, dot: css.col.slate500 },
  };
  const m = map[tone];
  const h = 24;
  const w = label.length * 6.4 + 30;
  const f = frame("tag " + tone, x, y, w, h, m.bg, h / 2);
  ellipse(f, 10, (h - 6) / 2, 6, 6, m.dot);
  const t = text(f, 22, (h - 16) / 2, w - 26, label, 12, 600, m.tx, {});
  t.textAutoResize = "HEIGHT";
  t.resize(w - 26, t.height);
  t.x = 22;
  return f;
}

function chip(parent, x, y, label, active) {
  const h = 34;
  const w = label.length * 7.2 + 26;
  const f = frame("chip", x, y, w, h, active ? css.col.ink : css.col.white, 17);
  if (!active) { f.strokes = [{ type: "SOLID", color: css.col.slate200 }]; f.strokeWeight = 1; }
  const t = text(f, 0, (h - 18) / 2, w, label, 13, active ? 600 : 600, active ? css.col.white : css.col.slate700, { align: "CENTER" });
  t.textAutoResize = "HEIGHT";
  t.resize(w, t.height);
  t.x = 0;
  return f;
}

// ---------------------------------------------------------------------
//  Inputs
// ---------------------------------------------------------------------
function field(parent, x, y, w, label, placeholder, opts) {
  let yy = y;
  if (label) {
    text(parent, x, yy, w, label, 13, 600, css.col.slate700, { name: "label", lineHeight: 18 });
    yy += 24;
  }
  const h = opts && opts.height ? opts.height : 44;
  const f = frame("input", x, yy, w, h, css.col.white, 8, { stroke: css.col.slate300, strokeWeight: 1 });
  const ph = text(f, 12, (h - 17) / 2, w - 40, placeholder, 14, 400, css.col.slate400, { name: "placeholder" });
  ph.textAutoResize = "WIDTH_AND_HEIGHT";
  if (opts && opts.select) {
    icon(f, w - 26, (h - 16) / 2, "chev", css.col.slate500, 16, 2);
  }
  if (opts && opts.eye) icon(f, w - 32, (h - 16) / 2, "eye", css.col.slate400, 16, 1.8);
  if (opts && opts.icon) {
    icon(f, 12, (h - 16) / 2, opts.icon, css.col.slate400, 16, 2);
    ph.x = 38;
  }
  if (opts && opts.error) text(parent, x, yy + h + 6, w, opts.error, 12, 500, css.col.red, { name: "field-error" });
  if (opts && opts.hint) text(parent, x, yy + h + 6, w, opts.hint, 12, 400, css.col.slate500, { name: "hint" });
  return f;
}

function checkRow(parent, x, y, label) {
  const cb = frame("checkbox", x, y, 18, 18, css.col.white, 4, { stroke: css.col.slate300, strokeWeight: 1.5 });
  icon(cb, 3, 3, "check", css.col.ink, 12, 2);
  text(parent, x + 28, y - 2, 320, label, 14, 400, css.col.slate600, { lineHeight: 20 });
}

// ---------------------------------------------------------------------
//  Avatar
// ---------------------------------------------------------------------
function avatar(parent, x, y, size, initials, opts) {
  const f = frame("avatar", x, y, size, size, (opts && opts.bg) || css.col.slate200, size / 2, opts && { clip: false });
  const fs = Math.max(9, Math.round(size * 0.34));
  const t = text(f, 0, (size - fs * 1.3) / 2, size, initials, fs, 700, css.col.slate700, { align: "CENTER" });
  t.textAutoResize = "HEIGHT";
  t.resize(size, t.height);
  t.x = 0;
  return f;
}

// ---------------------------------------------------------------------
//  Cards / panels
// ---------------------------------------------------------------------
function panel(parent, x, y, w, h, fill, radius) {
  return frame("panel", x, y, w, h, fill || css.col.white, radius || 14, { stroke: css.col.slate200, strokeWeight: 1, shadowSm: true });
}

function panelHeader(parent, x, y, w, title, sub, linkLabel) {
  text(parent, x, y, w * 0.62, title, 17, 700, css.col.ink, { name: "panel-title", lineHeight: 24 });
  if (sub) text(parent, x, y + 24, w * 0.62, sub, 13, 400, css.col.slate500, { name: "panel-sub", lineHeight: 18 });
  if (linkLabel) textLink(parent, x + w - (linkLabel.length * 6.9 + 24), y + 3, linkLabel, true);
}

function summaryIcon(parent, x, y, tone, iconName, size) {
  size = size || 40;
  const tones = {
    slate: { bg: css.col.slate100, ic: css.col.slate600 },
    green: { bg: css.col.green50, ic: css.col.green },
    blue: { bg: css.col.blue50, ic: css.col.blue },
    amber: { bg: css.col.amber50, ic: css.col.amber },
    red: { bg: css.col.red50, ic: css.col.red },
  };
  const m = tones[tone];
  const f = frame("summary-icon " + tone, x, y, size, size, m.bg, 10);
  icon(f, (size - 20) / 2, (size - 20) / 2, iconName, m.ic, 20, 2);
  return f;
}

function metricCard(parent, x, y, w, label, iconName, iconTone, value, footParts) {
  const f = frame("metric-card", x, y, w, 118, css.col.white, 14, { stroke: css.col.slate200, strokeWeight: 1, shadowSm: true });
  // top row
  const lbl = text(f, 20, 18, w - 90, label, 13, 600, css.col.slate600, { name: "metric-label" });
  const mi = summaryIcon(f, w - 60, 16, iconTone, iconName, 40);
  // value
  text(f, 20, 46, w - 40, value, 28, 700, css.col.ink, { name: "metric-value", lineHeight: 34 });
  // footer
  let fxx = 20;
  footParts.forEach((fp, i) => {
    if (fp.type === "text") {
      text(f, fxx, 88, null, fp.value, 13, fp.weight || 400, fp.color || css.col.slate500, { name: "metric-foot", lineHeight: 18 });
      fxx += fp.value.length * (13 * 0.55);
    } else if (fp.type === "link") {
      const lnk = text(f, fxx, 88, null, fp.value, 13, 600, css.col.ink, { lineHeight: 18 });
      icon(f, fxx + lnk.width + 3, 88, "arrow", css.col.ink, 14, 2);
      fxx += lnk.width + 19;
    }
  });
  return f;
}

function miniStat(parent, x, y, w, label, value, tone, iconName) {
  const f = frame("mini-stat", x, y, w, 64, css.col.white, 12, { stroke: css.col.slate200, strokeWeight: 1, shadowSm: true });
  const si = summaryIcon(f, 12, 12, tone, iconName, 40);
  text(f, 64, 14, w - 76, label, 12, 400, css.col.slate500, { name: "stat-label", lineHeight: 16 });
  text(f, 64, 30, w - 76, value, 18, 700, css.col.ink, { name: "stat-value", lineHeight: 22 });
  return f;
}

// ---------------------------------------------------------------------
//  Tables
// ---------------------------------------------------------------------
function tableHead(parent, x, y, headers, widths, alignR) {
  let xx = x;
  headers.forEach((h, i) => {
    const w = widths[i];
    text(parent, xx, y, w, h, 11, 600, css.col.slate500, { name: "th " + h, ls: 0.4, align: alignR && alignR[i] ? "RIGHT" : "LEFT" });
    xx += w;
  });
}

function tableRow(parent, x, y, widths, cells, rowH) {
  rowH = rowH || 60;
  rect(parent, x, y, widths.reduce((a, b) => a + b, 0), 1, css.col.slate100);
  let xx = x;
  cells.forEach((c, i) => {
    if (c) c(parent, xx, y, widths[i], rowH);
    xx += widths[i];
  });
}

// ---------------------------------------------------------------------
//  Delete modal + toast + notification panel
// ---------------------------------------------------------------------
function deleteModal(parent, x, y, targetName) {
  const w = 440;
  const h = 260;
  const wrap = frame("modal-backdrop", x, y, w, h, null);
  wrap.effects = [css.col.shadow];
  const card = frame("modal", x, y, w, h, css.col.white, 16);
  iconButton(card, w - 48, 16, 32, "x", null, 8);
  summaryIcon(card, 196, 28, "red", "trash", 48);
  text(card, 0, 96, w, "Delete this record?", 18, 700, css.col.ink, { align: "CENTER", lineHeight: 26 });
  text(card, 40, 128, w - 80, "You are about to delete " + targetName + ". This action cannot be undone.", 14, 400, css.col.slate600, { align: "CENTER", lineHeight: 20 });
  const cancel = button(card, 100, 184, 116, "Cancel", "secondary");
  const yes = button(card, 224, 184, 116, "Yes, delete", "danger");
  return wrap;
}

function toast(parent, x, y, message) {
  const f = frame("toast", x, y, 380, 64, css.col.white, 12, { stroke: css.col.slate200, strokeWeight: 1, shadow: true });
  summaryIcon(f, 14, 12, "green", "check", 40);
  text(f, 66, 12, 260, "Success", 14, 700, css.col.ink, { lineHeight: 18 });
  text(f, 66, 30, 260, message, 13, 400, css.col.slate500, { lineHeight: 18 });
  icon(f, 352, 24, "x", css.col.slate400, 16, 2);
  return f;
}

function notificationPanel(parent, x, y) {
  const w = 360;
  const f = frame("notification-panel", x, y, w, 232, css.col.white, 14, { stroke: css.col.slate200, strokeWeight: 1, shadow: true });
  text(f, 24, 20, 200, "Notifications", 18, 700, css.col.ink, { lineHeight: 24 });
  text(f, 24, 42, 200, "Updates", 11, 600, css.col.slate500, { ls: 1 });
  iconButton(f, w - 44, 16, 28, "x", null, 8);
  rect(f, 20, 74, w - 40, 1, css.col.slate100);
  summaryIcon(f, 24, 92, "blue", "bell", 36);
  text(f, 72, 92, w - 92, "Review your latest activity", 13, 600, css.col.slate700, { lineHeight: 18 });
  text(f, 72, 112, w - 92, "Open the dashboard to see recent updates.", 12, 400, css.col.slate500, { lineHeight: 16 });
  text(f, 72, 130, w - 92, "Just now", 11, 500, css.col.slate400, { lineHeight: 16 });
  const b = button(f, 20, 168, w - 40, "Mark all as read", "ghost", null, 40);
  return f;
}

// ---------------------------------------------------------------------
//  Placeholder avatar initials / helpers for forms
// ---------------------------------------------------------------------
function formSection(parent, x, y, w, num, title, sub) {
  text(parent, x, y, 24, num, 22, 700, css.col.slate200, { name: "section-number", lineHeight: 28 });
  text(parent, x + 32, y, w, title, 16, 700, css.col.ink, { lineHeight: 22 });
  text(parent, x + 32, y + 24, w, sub, 13, 400, css.col.slate500, { lineHeight: 18 });
  return y + 58;
}

function submitRow(parent, x, y, w, primaryLabel, secondaryLabel) {
  const ps = text(parent, x + w - 12, y, primaryLabel, 13, 400, css.col.slate500, { align: "RIGHT" });
  if (secondaryLabel) {
    const s = button(parent, x, y, secondaryLabel.length * 15, 44, secondaryLabel, "secondary");
    return;
  }
  button(parent, x, y, primaryLabel.length * 12, 44, primaryLabel, "primary");
}

// ---------------------------------------------------------------------
//  Pages & auto-placement
// ---------------------------------------------------------------------
function newPage(name) {
  const p = figma.createPage();
  p.name = name;
  return p;
}

function place(page, w, h, label) {
  if (!page.__c) page.__c = { x: 32, y: 32, rowH: 0, rowCount: 0 };
  const c = page.__c;
  const X = c.x, Y = c.y;
  c.x += w + 40;
  c.rowH = Math.max(c.rowH, h);
  c.rowCount++;
  if (c.rowCount >= 3) { c.x = 32; c.y += c.rowH + 60; c.rowH = 0; c.rowCount = 0; }
  const f = figma.createFrame();
  f.name = label;
  f.x = Math.round(X);
  f.y = Math.round(Y);
  f.resize(w, h);
  f.fills = [{ type: "SOLID", color: css.col.white }];
  f.clipsContent = true;
  page.appendChild(f);
  return f;
}

// ---------------------------------------------------------------------
//  Section 01 — Landing & Authentication
// ---------------------------------------------------------------------
const S1 = [
  ["01 · Landing  —  /", "landing"],
  ["01 · Login  —  /login", "login"],
  ["01 · Login — validation error  —  /login", "login-error"],
  ["01 · Register  —  /register", "register"],
  ["01 · Forgot password  —  /forgot-password", "forgot"],
  ["01 · Forgot password — link sent  —  /forgot-password", "forgot-sent"],
  ["01 · Reset password  —  /reset-password/{token}", "reset"],
  ["01 · Verify email  —  /verify-email", "verify"],
  ["01 · Verify email — resent  —  /verify-email", "verify-sent"],
  ["01 · Confirm password  —  /confirm-password", "confirm"],
];

function alertBanner(parent, x, y, w, tone, title, items) {
  const tones = { danger: { bg: css.col.red50, tx: css.col.red700 }, success: { bg: css.col.green50, tx: css.col.green700 } };
  const m = tones[tone];
  const h = items ? 74 : 48;
  const f = frame("alert " + tone, x, y, w, h, m.bg, 10);
  const iconName = tone === "danger" ? "alert" : "check";
  summaryIcon(f, 12, (h - 40) / 2, tone === "danger" ? "red" : "green", iconName, 40);
  text(f, 64, 14, w - 80, title, 13, 600, m.tx, { lineHeight: 18 });
  if (items) {
    let yy = 36;
    items.forEach((it) => {
      text(f, 80, yy, w - 96, "• " + it, 12, 400, m.tx, { lineHeight: 16 });
      yy += 16;
    });
  }
  return f;
}

function authCard(parent, w, draw) {
  const card = frame("login-card", 64, 120, w - 128, 660, css.col.white, 20, { shadow: true });
  draw(card);
  return card;
}

function authShowcase(parent) {
  const W = 790;
  const k = frame("login-showcase", 0, 0, W, 900, null);
  k.fills = [{ type: "GRADIENT_LINEAR", gradientTransform: [[0, 1, 0], [-1, 1, 0.5]], gradientStops: [{ position: 0, color: hex2rgb("#0f172a") }, { position: 1, color: hex2rgb("#1e293b") }] }];
  // decorative ring
  const ring = frame("decoration-ring", -160, -160, 520, 520, null);
  const rg = frame("decoration-ring2", 0, 0, 520, 520, null, undefined, { clip: false });
  rg.fills = [];
  rg.strokes = [{ type: "SOLID", color: hex2rgb("#334155") }];
  rg.strokeWeight = 1;
  rg.opacity = 0.55;
  const ring2 = rg.clone();
  ring2.resize(360, 360);
  ring2.x = 40;
  ring2.y = 40;
  rg.clipsContent = false;
  ring.appendChild(rg);
  parent.appendChild(ring);
  const ring3 = ring.clone();
  ring3.x = parent.width - 320;
  ring3.y = 560;
  ring3.resize(420, 420);
  ring3.children[0].children.forEach((n) => { n.rotation = 0; });
  // brand
  const brand = frame("brand-mark", 40, 40, 36, 36, css.col.white, 10);
  icon(brand, 8, 8, "home", css.col.slate900, 20, 2);
  text(parent, 88, 46, 220, "StayTrack", 16, 700, css.col.white, { name: "brand-name", lineHeight: 20 });
  text(parent, 88, 66, 220, "Apartment Management", 12, 400, css.col.slate400, { name: "brand-tag", lineHeight: 16 });
  // copy
  text(parent, 40, 290, 460, "ONE PLACE. EVERY PROPERTY DETAIL.", 12, 600, css.col.slate400, { name: "eyebrow-light", ls: 1.6, lineHeight: 18 });
  text(parent, 40, 318, 640, "Run your apartments\nwith clarity.", 40, 700, css.col.white, { name: "headline", lineHeight: 50 });
  text(parent, 40, 430, 560, "Track units, tenants, leases, bills, and payments from a calm, reliable workspace built for property teams.", 16, 400, hex2rgb("#cbd5e1"), { name: "intro", lineHeight: 26 });
  // proof stats
  const stats = frame("login-proof", 40, 560, 640, 90, null);
  const items = [["24", "Units managed"], ["96%", "Occupancy"], ["₱286K", "Collected this month"]];
  items.forEach((it, i) => {
    const bx = i * 214;
    text(stats, bx, 0, 200, it[0], 30, 700, css.col.white, { lineHeight: 36 });
    text(stats, bx, 42, 200, it[1], 13, 400, css.col.slate400, { lineHeight: 18 });
  });
  text(parent, 40, 852, 400, "Secure property operations for Metro Manila teams", 12, 400, css.col.slate500, { name: "login-footer", lineHeight: 18 });
}

function authScreen(container, viewId, draw) {
  authShowcase(container);
  const panel = frame("login-panel", 790, 0, 650, 900, css.col.white, null);
  authCard(panel, 650, (card) => {
    draw(card);
  });
}

function buildS1(page) {
  for (const [label, id] of S1) {
    const c = place(page, css.pageW, 900, label);
    authScreen(c, id, (card) => {
      const CW = card.width;
      if (id === "landing") {
        eyebrow(card, 0, 90, "Welcome", css.col.slate600);
        text(card, 0, 116, CW, "Your apartments, organized.", 30, 700, css.col.ink, { lineHeight: 40 });
        text(card, 0, 168, CW, "Sign in to manage units, tenants, leases, bills, and payments from one workspace.", 14, 400, css.col.slate600, { lineHeight: 21 });
        button(card, 0, 230, CW, "Sign in", "primary", "arrow", 52);
        const div = frame("or-divider", 0, 308, CW, 20, null);
        rect(div, 0, 9, CW * 0.44, 1, css.col.slate200);
        text(div, CW * 0.44 + 8, 0, null, "or explore another view", 12, 400, css.col.slate500, { align: "CENTER" });
        rect(div, CW * 0.44 + 8 + text(div, 0, 0, null, "x", 1, 400, css.col.slate500).width + 16, 9, CW * 0.46 - 28, 1, css.col.slate200);
        button(card, 0, 344, CW, "Create an account", "secondary", "user", 52);
        const help = frame("login-help", 0, 424, CW, 22, null);
        text(help, 0, 0, 200, "New tenant?", 13, 400, css.col.slate600, { lineHeight: 18 });
        text(help, 90, 0, 200, "Create your account", 13, 600, css.col.ink, { lineHeight: 18 });
      }
      if (id === "login") {
        eyebrow(card, 0, 60, "Welcome back", css.col.slate600);
        text(card, 0, 86, CW, "Sign in to StayTrack", 28, 700, css.col.ink, { lineHeight: 38 });
        text(card, 0, 132, CW, "Enter your credentials to open the StayTrack workspace.", 14, 400, css.col.slate600, { lineHeight: 21 });
        const lx = 0;
        field(card, lx, 176, CW, "Email address", "you@example.com");
        const py = 176 + 68;
        text(card, lx, py, CW, "Password", 13, 600, css.col.slate700, { lineHeight: 18 });
        text(card, lx + CW - 110, py, 110, "Forgot password?", 13, 600, css.col.slate700, { align: "RIGHT", lineHeight: 18 });
        field(card, lx, py + 24, CW, null, "••••••••••", { eye: true });
        checkRow(card, lx, py + 94, "Keep me signed in on this device");
        button(card, lx, py + 136, CW, "Sign in", "primary", "arrow", 52);
        const help = frame("login-help", 0, py + 216, CW, 22, null);
        text(help, 0, 0, 160, "New to StayTrack?", 13, 400, css.col.slate600, { lineHeight: 18 });
        text(help, 132, 0, 200, "Create an account", 13, 600, css.col.ink, { lineHeight: 18 });
      }
      if (id === "login-error") {
        eyebrow(card, 0, 54, "Welcome back", css.col.slate600);
        text(card, 0, 80, CW, "Sign in to StayTrack", 28, 700, css.col.ink, { lineHeight: 38 });
        text(card, 0, 126, CW, "Enter your credentials to open the StayTrack workspace.", 14, 400, css.col.slate600, { lineHeight: 21 });
        alertBanner(card, 0, 168, CW, "danger", "There was a problem signing in.", ["These credentials do not match our records."]);
        field(card, 0, 268, CW, "Email address", "tenant1@gmail.com", { error: "The email field is required." });
        text(card, 0, 268 + 68, CW, "Password", 13, 600, css.col.slate700, { lineHeight: 18 });
        field(card, 0, 268 + 92, CW, null, "••••••••••", { eye: true, error: "The password field is required." });
        checkRow(card, 0, 268 + 68 + 24 + 70, "Keep me signed in on this device");
        button(card, 0, 268 + 96 + 96, CW, "Sign in", "primary", "arrow", 52);
        const help = frame("login-help", 0, 268 + 176, CW, 22, null);
        text(help, 0, 0, 160, "New to StayTrack?", 13, 400, css.col.slate600, { lineHeight: 18 });
        text(help, 132, 0, 200, "Create an account", 13, 600, css.col.ink, { lineHeight: 18 });
      }
      if (id === "register") {
        eyebrow(card, 0, 44, "Create your account", css.col.slate600);
        text(card, 0, 70, CW, "Join StayTrack", 28, 700, css.col.ink, { lineHeight: 38 });
        text(card, 0, 114, CW, "Set up your account to start managing apartments.", 14, 400, css.col.slate600, { lineHeight: 21 });
        field(card, 0, 156, CW, "Name", "Juan Dela Cruz");
        field(card, 0, 224, CW, "Email address", "you@example.com");
        field(card, 0, 292, CW, "Password", "••••••••••", { eye: true });
        field(card, 0, 360, CW, "Confirm password", "••••••••••", { eye: true });
        button(card, 0, 428, CW, "Create account", "primary", "arrow", 52);
        const help = frame("login-help", 0, 500, CW, 22, null);
        text(help, 0, 0, 160, "Already registered?", 13, 400, css.col.slate600, { lineHeight: 18 });
        text(help, 142, 0, 200, "Sign in", 13, 600, css.col.ink, { lineHeight: 18 });
      }
      if (id === "forgot" || id === "forgot-sent") {
        eyebrow(card, 0, 60, "Account recovery", css.col.slate600);
        text(card, 0, 86, CW, "Reset your password", 28, 700, css.col.ink, { lineHeight: 38 });
        text(card, 0, 132, CW, "Forgot your password? Enter your email and we will send you a link to choose a new one.", 14, 400, css.col.slate600, { lineHeight: 21 });
        if (id === "forgot-sent") {
          alertBanner(card, 0, 168, CW, "success", "We have emailed your password reset link!");
        }
        field(card, 0, id === "forgot" ? 178 : 232, CW, "Email address", "you@example.com");
        button(card, 0, id === "forgot" ? 246 : 300, CW, "Email password reset link", "primary", "arrow", 52);
        const help = frame("login-help", 0, id === "forgot" ? 322 : 376, CW, 22, null);
        text(help, 0, 0, 200, "Back to sign in", 13, 600, css.col.ink, { lineHeight: 18 });
      }
      if (id === "reset") {
        eyebrow(card, 0, 60, "Account recovery", css.col.slate600);
        text(card, 0, 86, CW, "Choose a new password", 28, 700, css.col.ink, { lineHeight: 38 });
        text(card, 0, 132, CW, "Enter a new password for your account to regain access to StayTrack.", 14, 400, css.col.slate600, { lineHeight: 21 });
        field(card, 0, 178, CW, "Email address", "you@example.com");
        field(card, 0, 246, CW, "New password", "••••••••••", { eye: true });
        field(card, 0, 314, CW, "Confirm new password", "••••••••••", { eye: true });
        button(card, 0, 382, CW, "Reset password", "primary", "arrow", 52);
      }
      if (id === "verify" || id === "verify-sent") {
        eyebrow(card, 0, 60, "Almost there", css.col.slate600);
        text(card, 0, 86, CW, "Verify your email", 28, 700, css.col.ink, { lineHeight: 38 });
        text(card, 0, 132, CW, "Thanks for signing up! Before getting started, please verify your email by clicking the link we just sent you.", 14, 400, css.col.slate600, { lineHeight: 21 });
        if (id === "verify-sent") {
          alertBanner(card, 0, 168, CW, "success", "A new verification link has been sent to the email address you provided during registration.");
        }
        button(card, 0, id === "verify" ? 196 : 252, CW, "Resend verification email", "primary", "arrow", 44);
        button(card, 0, id === "verify" ? 252 : 308, CW, "Log out", "secondary", null, 44);
      }
      if (id === "confirm") {
        eyebrow(card, 0, 60, "Secure area", css.col.slate600);
        text(card, 0, 86, CW, "Confirm your password", 28, 700, css.col.ink, { lineHeight: 38 });
        text(card, 0, 132, CW, "This is a secure area of the application. Please confirm your password before continuing.", 14, 400, css.col.slate600, { lineHeight: 21 });
        field(card, 0, 178, CW, "Password", "••••••••••", { eye: true });
        button(card, 0, 246, CW, "Confirm", "primary", "arrow", 52);
      }
    });
  }
}

// ---------------------------------------------------------------------
//  Section 02/03 — App shell (sidebar + topbar) + data
// ---------------------------------------------------------------------
function rgba(c, a) { return { r: c.r, g: c.g, b: c.b, a: a }; }

const APTS = [
  { unit: "A-101", type: "1 Bedroom", floor: "1st floor", rent: "₱800.00", bed: 1, bath: 1, status: ["occupied", "info"] },
  { unit: "A-001", type: "2 Bedroom", floor: "1st floor", rent: "₱8,000.00", bed: 2, bath: 1, status: ["occupied", "info"] },
  { unit: "A-002", type: "2 Bedroom", floor: "1st floor", rent: "₱10,000.00", bed: 2, bath: 1, status: ["available", "success"] },
  { unit: "B-101", type: "Studio", floor: "2nd floor", rent: "₱8,000.00", bed: 2, bath: 1, status: ["available", "success"] },
];
const TENANTS = [
  { name: "John Michael Carig", phone: "0927 587 5043", email: "carigjohnmichaelscarig@gmail.com", lease: "Active", initials: "JC" },
  { name: "Jm Carig", phone: "0927 587 5043", email: "carigjohnmichaelscarig@gmail.com", lease: "Active", initials: "JC" },
  { name: "Juanito Mico", phone: "0927 587 5043", email: "josa.carig.up@phinmaed.com", lease: "Active", initials: "JM" },
  { name: "Juan Dela Cruz", phone: "—", email: "jayboy@gmail.com", lease: "No lease", initials: "JD" },
  { name: "Emily Carig", phone: "—", email: "emz@gmail.com", lease: "No lease", initials: "EC" },
  { name: "Mickaela Jade Carig", phone: "0927 587 5043", email: "mikay@gmail.com", lease: "No lease", initials: "MJ" },
  { name: "Owner", phone: "—", email: "admin@gmail.com", lease: "No lease", initials: "O" },
  { name: "Tenant 1", phone: "0927 587 5043", email: "tenant1@gmail.com", lease: "No lease", initials: "T1" },
];
const LEASES = [
  { id: 1, tenant: "John Michael Carig", unit: "A-101", start: "Jul 29, 2026", end: "Aug 29, 2026", rent: "₱800.00", dep: "₱1,000.00" },
  { id: 2, tenant: "Jm Carig", unit: "A-101", start: "Jul 29, 2026", end: "Aug 29, 2026", rent: "₱800.00", dep: "₱1,000.00" },
  { id: 3, tenant: "Juanito Mico", unit: "A-001", start: "Aug 1, 2026", end: "Aug 1, 2027", rent: "₱8,000.00", dep: "₱0.00" },
];
const BILLS = [
  { id: 1, tenant: "John Michael Carig", unit: "A-101", month: "August 2026", due: "Jul 28, 2026", amount: "₱1,000.00", status: ["Paid", "success"] },
  { id: 2, tenant: "Jm Carig", unit: "A-101", month: "July 2026", due: "Aug 1, 2026", amount: "₱1,200.00", status: ["Unpaid", "neutral"] },
  { id: 3, tenant: "Juanito Mico", unit: "A-001", month: "August 2026", due: "Aug 31, 2026", amount: "₱8,500.00", status: ["Unpaid", "neutral"] },
];
const PAYMENTS = [
  { id: 1, tenant: "John Michael Carig", unit: "A-101", date: "Jul 29, 2026", method: "Cash", tag: "green", amount: "₱1,000.00", ref: "—", bill: 1, period: "August 2026" },
];
const MSTATUS = [["Pending", "warning"], ["In progress", "info"], ["Completed", "success"], ["Cancelled", "neutral"]];
const PRIORITY_COLORS = { High: css.col.red, Medium: css.col.amber, Low: css.col.slate600 };
const MREQ = [
  { id: 1, title: "Leaking faucet in bathroom", unit: "A-101", category: "Plumbing", priority: "High", status: ["Pending", "warning"], date: "Aug 10, 2026" },
  { id: 2, title: "Replace worn hallway bulb", unit: "A-002", category: "Electrical", priority: "Low", status: ["In progress", "info"], date: "Aug 14, 2026" },
  { id: 3, title: "AC unit not cooling", unit: "B-101", category: "Appliance", priority: "Medium", status: ["Completed", "success"], date: "Jun 2, 2026" },
];

function per(s) { return s[0]; }

function sidebarNav(sb, items, active) {
  let y = 104;
  items.forEach((it) => {
    if (it.label) {
      text(sb, 20, y, 200, it.label, 11, 600, css.col.slate500, { ls: 1, lineHeight: 16 });
      y += 26;
    } else {
      const on = it.id === active;
      const il = frame("nav-link", 14, y, 228, 40, on ? rgba({ r: 1, g: 1, b: 1 }, 0.09) : null, 8);
      icon(il, 14, 11, it.icon, on ? css.col.white : css.col.slate400, 18, 2);
      const t = text(il, 44, 12, 160, it.name, 14, on ? 600 : 500, on ? css.col.white : css.col.slate300, { lineHeight: 18 });
      y += 44;
    }
  });
  return y;
}

function appShell(container, opts) {
  const role = opts.role || "admin";
  const adminNav = [
    { label: true, name: "Main menu" },
    { id: "dashboard", name: "Dashboard", icon: "grid" },
    { label: true, name: "Property management" },
    { id: "apartments", name: "Apartments", icon: "building" },
    { id: "tenants", name: "Tenants", icon: "users" },
    { id: "leases", name: "Leases", icon: "file" },
    { id: "maintenance", name: "Maintenance", icon: "wrench" },
    { label: true, name: "Finance" },
    { id: "bills", name: "Bills", icon: "receipt" },
    { id: "payments", name: "Payments", icon: "card" },
    { label: true, name: "Account" },
    { id: "profile", name: "Profile", icon: "user" },
  ];
  const tenantNav = [
    { label: true, name: "Main menu" },
    { id: "dashboard", name: "My dashboard", icon: "grid" },
    { label: true, name: "My rental" },
    { id: "apartment", name: "My apartment", icon: "home" },
    { id: "lease", name: "My lease", icon: "file" },
    { id: "bills", name: "My bills", icon: "receipt" },
    { id: "payments", name: "Payment history", icon: "card" },
    { id: "maintenance", name: "Maintenance", icon: "wrench" },
    { label: true, name: "Account" },
    { id: "profile", name: "Profile", icon: "user" },
  ];
  const H = container.height;
  const sb = frame("app-sidebar", 0, 0, css.sidebarW, H, css.col.slate900, 0, { clip: false });
  const mark = brandMark(sb, 20, 18, 36);
  text(sb, 68, 22, 180, "StayTrack", 16, 700, css.col.white, { name: "brand-name", lineHeight: 20 });
  text(sb, 68, 44, 180, "Apartment Management", 11, 400, css.col.slate400, { name: "brand-tag", lineHeight: 15 });
  const nav = sidebarNav(sb, role === "admin" ? adminNav : tenantNav, opts.active);
  // sidebar account
  const acH = 116;
  const ac = frame("sidebar-account", 14, H - acH - 14, 228, 96, rgba({ r: 1, g: 1, b: 1 }, 0.06), 12);
  avatar(ac, 14, 14, 36, opts.initials, { bg: css.col.slate700 });
  text(ac, 60, 14, 160, opts.user, 13, 600, css.col.slate100, { lineHeight: 18 });
  text(ac, 60, 34, 160, opts.email, 11, 400, css.col.slate400, { lineHeight: 15 });
  const so = frame("sign-out", 14, H - 38, 100, 24, null);
  icon(so, 0, 2, "out", css.col.slate400, 16, 2);
  text(so, 22, 0, 80, "Sign out", 13, 500, css.col.slate400, { lineHeight: 18 });
  // topbar
  const tb = frame("topbar", css.sidebarW, 0, css.pageW - css.sidebarW, css.topbarH, css.col.white, 0, { clip: false });
  rect(tb, 0, css.topbarH - 1, tb.width, 1, css.col.slate200);
  text(tb, 32, 18, 320, "StayTrack Workspace", 12, 600, css.col.slate500, { name: "topbar-kicker", ls: 0.4, lineHeight: 16 });
  text(tb, 32, 38, 320, "Saturday, September 19, 2026", 13, 400, css.col.slate700, { name: "topbar-date", lineHeight: 18 });
  // bell
  const bell = iconButton(tb, tb.width - 200, 18, 36, "bell", null, 8);
  ellipse(bell, 22, 2, 8, 8, css.col.red);
  // profile chip
  const pr = frame("topbar-profile", tb.width - 152, 14, 150, 44, null);
  avatar(pr, 0, 4, 32, opts.initials, { bg: css.col.slate200 });
  text(pr, 42, 2, 100, opts.user.length > 17 ? opts.user.slice(0, 16) + "…" : opts.user, 13, 700, css.col.ink, { name: "name", lineHeight: 18 });
  text(pr, 42, 21, 100, opts.roleLabel, 11, 400, css.col.slate500, { name: "role", lineHeight: 14 });
  icon(pr, 100, 13, "chev", css.col.slate400, 16, 2);
  // content background
  rect(container, css.sidebarW, css.topbarH, css.pageW - css.sidebarW, H - css.topbarH, css.col.page);
  return { x: css.sidebarW + css.pad, y: css.topbarH + css.pad, w: css.pageW - css.sidebarW - css.pad * 2 };
}

// ---------------------------------------------------------------------
//  Shared content builders
// ---------------------------------------------------------------------
function backLink(parent, x, y, label) {
  icon(parent, x, y + 1, "back", css.col.slate600, 14, 2);
  text(parent, x + 18, y, null, label, 13, 600, css.col.slate600, { lineHeight: 18 });
}

function pageHead(parent, x, y, w, opts) {
  eyebrow(parent, x, y, opts.eyebrow);
  text(parent, x, y + 24, w * 0.6, opts.title, 26, 700, css.col.ink, { lineHeight: 34 });
  if (opts.sub) text(parent, x, y + 62, w * 0.66, opts.sub, 14, 400, css.col.slate600, { lineHeight: 20 });
  if (opts.actions) {
    let ax = x + w;
    for (let i = opts.actions.length - 1; i >= 0; i--) {
      const a = opts.actions[i];
      const bw = a.label.length * 9 + 64;
      ax -= bw;
      button(parent, ax, y + 20, bw, a.label, a.style, a.icon);
      ax -= 12;
    }
  }
  return y + 86;
}

function detailHero(parent, x, y, w, opts) {
  const f = frame("detail-hero", x, y, w, 168, css.col.white, 14, { stroke: css.col.slate200, strokeWeight: 1, shadowSm: true });
  const big = frame("detail-identity-icon", 28, 28, 64, 64, (opts.iconTone && opts.iconToneBg) || css.col.slate100, 14);
  icon(big, 20, 20, opts.icon || "home", (opts.iconTone && opts.iconToneIc) || css.col.slate700, 24, 2);
  eyebrow(f, 112, 30, opts.eyebrow);
  text(f, 112, 54, w - 200, opts.title, 22, 700, css.col.ink, { lineHeight: 30 });
  if (opts.sub) text(f, 112, 88, w - 220, opts.sub, 14, 400, css.col.slate600, { lineHeight: 20 });
  if (opts.badgeLabel) badge(f, 112, 116, opts.badgeLabel, opts.badgeTone, true);
  if (opts.action) {
    const bw = opts.action.label.length * 9 + 64;
    button(f, w - bw - 28, 28, bw, opts.action.label, opts.action.style, opts.action.icon);
  }
  return f;
}

function valueGrid(parent, x, y, w, items) {
  const n = items.length;
  const gap = 16;
  const cw = (w - (n - 1) * gap) / n;
  items.forEach((it, i) => {
    const cx = x + i * (cw + gap);
    const f = frame("value-card", cx, y, cw, 104, css.col.white, 12, { stroke: css.col.slate200, strokeWeight: 1 });
    text(f, 16, 16, cw - 32, it.label, 12, 500, css.col.slate500, { lineHeight: 16 });
    text(f, 16, 38, cw - 32, it.value, 19, 700, css.col.ink, { lineHeight: 26 });
    if (it.sub) text(f, 16, 70, cw - 32, it.sub, 12, 400, css.col.slate500, { lineHeight: 16 });
  });
  return y + 128;
}

// ---------------------------------------------------------------------
//  Generic editable table
// ---------------------------------------------------------------------
function renderCell(parent, x, y, cw, cell, rowH, align) {
  if (cell.type === "unit") {
    const bg = frame("unit-icon", x, y + (rowH - 40) / 2, 40, 40, css.col.slate100, 10);
    icon(bg, 10, 10, "building", css.col.slate700, 20, 2);
    text(parent, x + 50, y + 13, cw - 50, cell.v, 14, 700, css.col.ink, { lineHeight: 18 });
    text(parent, x + 50, y + 31, cw - 50, cell.sub, 12, 400, css.col.slate500, { lineHeight: 16 });
    return;
  }
  if (cell.type === "person") {
    avatar(parent, x, y + (rowH - 32) / 2, 32, cell.v2, { bg: css.col.slate200 });
    text(parent, x + 42, y + 12, cw - 44, cell.v, 14, 700, css.col.ink, { lineHeight: 18 });
    text(parent, x + 42, y + 30, cw - 44, cell.sub, 12, 400, css.col.slate500, { lineHeight: 16 });
    return;
  }
  if (cell.type === "badge") {
    const b = badge(parent, align === "RIGHT" ? x + cw - (cell.v.length * 6.4 + 18) : x, y + (rowH - 24) / 2, cell.v, cell.tone);
    return;
  }
  if (cell.type === "tag") {
    const t = tag(parent, align === "RIGHT" ? x + cw - (cell.v.length * 6.4 + 30) : x, y + (rowH - 24) / 2, cell.v, cell.tone);
    return;
  }
  if (cell.type === "actions") {
    const items = cell.actions || ["eye", "edit", "trash"];
    let ax = x + cw - items.length * 36;
    items.forEach((it) => {
      iconButton(parent, ax, y + (rowH - 32) / 2, 32, it, null, 6);
      ax += 36;
    });
    return;
  }
  if (cell.type === "text") {
    const fill = cell.color || (cell.muted ? css.col.slate500 : css.col.slate600);
    const weight = cell.weight || (cell.strong ? 700 : 500);
    const size = cell.size || (cell.strong ? 14 : 14);
    if (cell.strong) {
      text(parent, align === "RIGHT" ? x + cw - (cell.v.length * 8) : x, y + 11, cw, cell.v, 14, 700, css.col.ink, { lineHeight: 18, align: align === "RIGHT" ? "RIGHT" : "LEFT" });
      if (cell.sub) text(parent, x, y + 31, cw, cell.sub, 12, 500, cell.subColor || css.col.slate500, { lineHeight: 16, align: align === "RIGHT" ? "RIGHT" : "LEFT" });
    } else {
      text(parent, x, y + (rowH - 18) / 2, cw, cell.v, size, weight, fill, { lineHeight: 18, align: align === "RIGHT" ? "RIGHT" : "LEFT" });
    }
    return;
  }
  if (cell.type === "empty") {
    text(parent, x, y + (rowH - 18) / 2, cw, cell.v, 14, 400, css.col.slate500, { lineHeight: 18, align: "CENTER" });
    return;
  }
  text(parent, x, y + (rowH - 18) / 2, cw, cell.v, 14, 500, css.col.slate600, { lineHeight: 18, align: align === "RIGHT" ? "RIGHT" : "LEFT" });
}

function simpleTable(parent, x, y, w, colDefs, rows, opts) {
  const headerH = opts && opts.headerH !== undefined ? opts.headerH : 44;
  const rowH = opts && opts.rowH !== undefined ? opts.rowH : 60;
  const hdr = frame("table-header", x, y, w - (opts && opts.extraW || 0), headerH, css.col.slate50, 0, { clip: false });
  rect(parent, x, y, w, 1, css.col.slate100);
  let hx = x + 20;
  colDefs.forEach((c) => {
    const cw = c.w - (opts && opts.extraX || 0);
    if (c.align === "RIGHT") {
      text(parent, hx + cw - (c.t.length * 6.2), y + 15, cw, c.t, 11, 600, css.col.slate500, { ls: 0.5, lineHeight: 15 });
    } else {
      text(parent, hx, y + 15, cw, c.t, 11, 600, css.col.slate500, { ls: 0.5, lineHeight: 15 });
    }
    hx += c.w;
  });
  let ry = y + headerH;
  rows.forEach((row, ri) => {
    rect(parent, x, ry, w, rowH, css.col.white);
    if (ri > 0) rect(parent, x, ry, w, 1, css.col.slate100);
    let cx = x + 20;
    row.forEach((cell, ci) => {
      const cw = colDefs[ci].w - 40;
      renderCell(parent, cx, ry, cw, cell, rowH, colDefs[ci].align);
      cx += colDefs[ci].w;
    });
    ry += rowH;
  });
  return ry;
}

// ---------------------------------------------------------------------
//  Section 02 — Admin screens
// ---------------------------------------------------------------------
function sAdminDashboard(container) {
  const a = appShell(container, { role: "admin", active: "dashboard", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "Admin portal",
    title: "Welcome back, StayTrack Admin",
    sub: "Here is what is happening across your properties today.",
    actions: [
      { label: "Record payment", style: "secondary", icon: "card" },
      { label: "Create lease", style: "primary", icon: "plus" },
    ],
  });
  const gap = 20;
  const mw = (W - gap * 3) / 4;
  metricCard(container, X, y, mw, "Total apartments", "building", "slate", "4", [
    { type: "text", value: "2 occupied", color: css.col.green, weight: 600 },
    { type: "link", value: "View apartments" },
  ]);
  metricCard(container, X + mw + gap, y, mw, "Occupied units", "home", "green", "2", [
    { type: "text", value: "50% occupancy", color: css.col.slate600, weight: 600 },
    { type: "text", value: "  2 available", color: css.col.slate500 },
  ]);
  metricCard(container, X + (mw + gap) * 2, y, mw, "Active tenants", "users", "blue", "8", [
    { type: "text", value: "10 total", color: css.col.green, weight: 600 },
    { type: "link", value: "View tenants" },
  ]);
  metricCard(container, X + (mw + gap) * 3, y, mw, "September revenue", "wallet", "amber", "₱0", [
    { type: "text", value: "collected this month", color: css.col.green, weight: 600 },
    { type: "link", value: "View payments" },
  ]);
  y += 118 + 24;
  // dashboard grid
  const right = 365;
  const left = W - right - gap;
  const p1 = panel(container, X, y, left, 300, css.col.white, 14);
  panelHeader(p1, 20, 20, left - 40, "Recent payments", "Latest tenant transactions", "View all");
  const pw = left;
  simpleTable(container, X, y + 76, pw, [
    { t: "TENANT", w: 250 }, { t: "UNIT", w: 100 }, { t: "DATE", w: 120 }, { t: "METHOD", w: 130 }, { t: "AMOUNT", w: pw - 600, align: "RIGHT" },
  ], [
    [
      { type: "person", v2: "JC", v: "John Michael Carig", sub: "Bill #1" },
      { type: "text", v: "A-101" },
      { type: "text", v: "Jul 29, 2026" },
      { type: "tag", v: "Cash", tone: "green" },
      { type: "text", v: "₱1,000.00", strong: true, sub: "Paid", subColor: css.col.green },
    ],
    [
      { type: "empty", v: "No more payments recorded yet." },
      { type: "empty", v: "" }, { type: "empty", v: "" }, { type: "empty", v: "" }, { type: "empty", v: "" },
    ],
  ], { rowH: 64, headerH: 44 });
  const p2 = panel(container, X + left + gap, y, right, 300, css.col.white, 14);
  panelHeader(p2, 20, 20, right - 40, "Billing overview", "Current financial status");
  const rows = [
    ["Active leases", "3", "file", "blue"],
    ["Unpaid / partial", "2", "receipt", "amber"],
    ["Overdue bills", "0", "alert", "red"],
  ];
  rows.forEach((r, i) => {
    const ry = 70 + i * 52;
    const f = frame("summary-row", 20, ry, right - 40, 40, null);
    summaryIcon(f, 0, 0, r[3], r[2], 36);
    text(f, 46, 11, 120, r[0], 13, 400, css.col.slate600, { lineHeight: 18 });
    const col = r[3] === "red" ? css.col.red : r[3] === "amber" ? css.col.amber : css.col.ink;
    text(f, 150, 8, 60, r[1], 16, 700, col, { align: "RIGHT", lineHeight: 22 });
  });
  button(p2, 20, 224, right - 40, "Manage bills", "primary", "receipt", 44);
  y += 300 + 24;
  // lower grid
  const p3 = panel(container, X, y, 365, 320, css.col.white, 14);
  panelHeader(p3, 20, 20, 325, "Apartment overview", "Live unit distribution");
  const don = ellipseArc(p3, 182, 168, 86, 22, 0, 180, css.col.ink);
  ellipseArc(p3, 182, 168, 86, 22, 180, 360, css.col.green);
  text(p3, 0, 152, 365, "50%", 26, 700, css.col.ink, { align: "CENTER", lineHeight: 34 });
  text(p3, 0, 188, 365, "Occupied", 12, 400, css.col.slate500, { align: "CENTER", lineHeight: 16 });
  const legend = [
    ["occupied", "Occupied", "2", css.col.ink],
    ["available", "Available", "2", css.col.green],
    ["reserved", "Reserved", "0", css.col.amber],
    ["maintenance", "Maintenance", "0", css.col.red],
  ];
  legend.forEach((l, i) => {
    const ly = y + 228 + i * 22 + 16;
    ellipse(container, X + 48, ly, 10, 10, l[3]);
    text(container, X + 66, ly - 4, 120, l[1], 13, 400, css.col.slate600, { lineHeight: 18 });
    text(container, X + 300, ly - 4, 40, l[2], 13, 700, css.col.ink, { align: "RIGHT", lineHeight: 18 });
  });
  const p4 = panel(container, X + 365 + gap, y, left + right - 385 - gap, 320, css.col.white, 14);
  panelHeader(p4, 20, 20, left + right - 385 - gap - 40, "Recent leases", "Latest rental agreements", "View all");
  let ly0 = 76;
  LEASES.forEach((l) => {
    const f = frame("lease-row", 20, ly0, left + right - 385 - gap - 40, 68, null);
    summaryIcon(f, 0, 14, "green", "file", 40);
    text(f, 52, 16, 320, l.tenant, 14, 700, css.col.ink, { lineHeight: 18 });
    text(f, 52, 36, 380, "Unit " + l.unit + "  ·  " + l.start + " – " + l.end, 12, 400, css.col.slate500, { lineHeight: 16 });
    badge(f, f.width - 116, 22, "Active", "success");
    icon(f, f.width - 16, 26, "chev", css.col.slate400, 16, 2);
    ly0 += 76;
  });
}

function sApartmentsList(container) {
  const a = appShell(container, { active: "apartments", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "Property management",
    title: "Apartments",
    sub: "Manage inventory, rates, and availability across all units.",
    actions: [{ label: "Add apartment", style: "primary", icon: "plus" }],
  });
  const gap = 20;
  const mw = (W - gap * 3) / 4;
  miniStat(container, X, y, mw, "Total units", "4", "slate", "building");
  miniStat(container, X + mw + gap, y, mw, "Available", "2", "green", "check");
  miniStat(container, X + (mw + gap) * 2, y, mw, "Occupied", "2", "blue", "home");
  miniStat(container, X + (mw + gap) * 3, y, mw, "Maintenance", "0", "red", "alert");
  y += 64 + 24;
  const p = panel(container, X, y, W, 420, css.col.white, 14);
  // toolbar
  const sf = frame("search-field", 20, 20, 300, 40, css.col.white, 10, { stroke: css.col.slate200, strokeWeight: 1 });
  icon(sf, 12, 12, "search", css.col.slate400, 16, 2);
  text(sf, 36, 11, 240, "Search unit or type…", 13, 400, css.col.slate400, { lineHeight: 18 });
  const chips = ["All", "Available", "Occupied", "Maintenance"];
  let cx0 = 340;
  chips.forEach((c, i) => {
    chip(p, cx0, 13, c, i === 0);
    cx0 += c.length * 7.2 + 34;
  });
  rect(p, 0, 72, W, 1, css.col.slate100);
  const rows = APTS.map((ap) => [
    { type: "unit", v: ap.unit, sub: ap.bed + " bed · " + ap.bath + " bath" },
    { type: "text", v: ap.type },
    { type: "text", v: ap.floor },
    { type: "text", v: ap.rent, strong: true },
    { type: "badge", v: ap.status[0], tone: ap.status[1] },
    { type: "actions" },
  ]);
  simpleTable(container, X, y + 72, W, [
    { t: "UNIT", w: 250 }, { t: "TYPE", w: 160 }, { t: "FLOOR", w: 130 }, { t: "MONTHLY RENT", w: 170 }, { t: "STATUS", w: 150 }, { t: "ACTIONS", w: W - 860, align: "RIGHT" },
  ], rows, { rowH: 64 });
}

function sApartmentForm(container, mode) {
  const isEdit = mode === "edit";
  const a = appShell(container, { active: "apartments", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to apartments");
  let y = Y + 40;
  pageHead(container, X, y, W, {
    eyebrow: "Apartments",
    title: isEdit ? "Edit Apartment A-101" : "Add apartment",
    sub: "Unit details, rates, and availability.",
  });
  y += 96;
  const p = panel(container, X, y, W, 470, css.col.white, 14);
  const sec = frame("form-section", 20, 20, W - 40, 60, null);
  text(sec, 0, 0, 24, "1", 22, 700, css.col.slate200, { lineHeight: 28 });
  text(sec, 32, 0, 400, "Unit information", 16, 700, css.col.ink, { lineHeight: 22 });
  text(sec, 32, 26, 400, "Define the unit number, floor, and rental rates.", 13, 400, css.col.slate500, { lineHeight: 18 });
  const fields = [
    ["Unit number *", "A-101", ""], ["Floor *", "1st floor", ""],
    ["Unit type *", "Select unit type", ""], ["Monthly rent", "₱ 800.00", "₱"],
    ["Security deposit", "₱ 800.00", "₱"], ["Bedrooms", "1", ""], ["Status", "Select status", "occupied"],
  ];
  const colW = (W - 88) / 2;
  fields.forEach((f, i) => {
    const fx = X + 20 + (i % 2) * (colW + 24);
    const fy = y + 100 + Math.floor(i / 2) * 96;
    const isSelect = f[0].indexOf("type") >= 0 || f[0] === "Status" || f[0] === "Bedrooms";
    field(container, fx, fy, colW, f[0], f[1], isSelect ? { select: true } : {});
  });
  // footer
  const fy = y + 400;
  text(container, X + 20, fy, 400, "Press Enter to save or Cancel to discard.", 12, 400, css.col.slate400, { lineHeight: 16 });
  button(container, X + W - 264, fy - 4, 120, "Cancel", "secondary");
  button(container, X + W - 132, fy - 4, 112, isEdit ? "Save changes" : "Save apartment", "primary");
}

function sApartmentShow(container) {
  const a = appShell(container, { active: "apartments", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to apartments");
  let y = Y + 40;
  detailHero(container, X, y, W, {
    icon: "building", iconToneBg: css.col.slate100, iconToneIc: css.col.slate700,
    eyebrow: "Unit", title: "Apartment A-101", sub: "1 Bedroom · 1st floor",
    badgeLabel: "Occupied", badgeTone: "info",
    action: { label: "Edit apartment", style: "secondary", icon: "edit" },
  });
  y += 168 + 24;
  valueGrid(container, X, y, W, [
    { label: "Monthly rent", value: "₱800.00" },
    { label: "Security deposit", value: "₱800.00" },
    { label: "Bedrooms", value: "1" },
  ]);
  y += 128 + 24;
  const p = panel(container, X, y, W, 150, css.col.white, 14);
  panelHeader(p, 20, 20, W - 40, "Unit overview", "Configuration and rental info");
  text(p, 20, 60, W - 40, "A compact one-bedroom unit on the first floor of the building. Ideal for solo tenants.", 14, 400, css.col.slate600, { lineHeight: 22 });
}

function sTenantsList(container, quickOpen) {
  const a = appShell(container, { active: "tenants", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "People",
    title: "Tenants",
    sub: "Keep resident contact details, status, and lease context organized.",
    actions: [{ label: "Add tenant", style: "primary", icon: "plus" }],
  });
  if (quickOpen) {
    const qc = panel(container, X, y, W, 150, css.col.white, 14);
    text(qc, 20, 18, 300, "New tenant", 15, 700, css.col.ink, { lineHeight: 21 });
    text(qc, 20, 42, 500, "Add a resident profile.", 13, 400, css.col.slate500, { lineHeight: 18 });
    field(container, X + 20, y + 68, 300, "First name *", "First name", {});
    field(container, X + 336, y + 68, 260, "Last name *", "Last name", {});
    button(container, X + W - 140, y + 92, 120, "Add tenant", "primary", "plus");
    y += 150 + 24;
  } else {
    y += 24;
  }
  const p = panel(container, X, y, W, 30, css.col.white, 14);
  const sf = frame("search-field", 20, 20, 300, 40, css.col.white, 10, { stroke: css.col.slate200, strokeWeight: 1 });
  icon(sf, 12, 12, "search", css.col.slate400, 16, 2);
  text(sf, 36, 11, 240, "Search tenants…", 13, 400, css.col.slate400, { lineHeight: 18 });
  const rows = TENANTS.map((t) => [
    { type: "person", v2: t.initials, v: t.name, sub: t.email },
    { type: "text", v: t.phone },
    t.lease === "Active" ? { type: "badge", v: "Active lease", tone: "success" } : { type: "badge", v: "No lease", tone: "neutral" },
    { type: "actions" },
  ]);
  simpleTable(container, X, y + 72, W, [
    { t: "TENANT", w: 360 }, { t: "CONTACT", w: 260 }, { t: "LEASE", w: 160 }, { t: "ACTIONS", w: W - 780, align: "RIGHT" },
  ], rows, { rowH: 72 });
}

function sTenantShow(container) {
  const a = appShell(container, { active: "tenants", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to tenants");
  let y = Y + 40;
  const hero = frame("detail-hero", X, y, W, 168, css.col.white, 14, { stroke: css.col.slate200, strokeWeight: 1, shadowSm: true });
  avatar(hero, 28, 40, 64, "JC", { bg: css.col.slate200 });
  eyebrow(hero, 112, 34, "Tenant");
  text(hero, 112, 58, W - 200, "Jm Carig", 22, 700, css.col.ink, { lineHeight: 30 });
  text(hero, 112, 92, W - 200, "carigjohnmichaelscarig@gmail.com", 14, 400, css.col.slate600, { lineHeight: 20 });
  badge(hero, 112, 120, "Active", "success", true);
  button(hero, W - 180, 62, 132, "Edit tenant", "secondary", "edit");
  y += 168 + 24;
  const p = panel(container, X, y, W, 260, css.col.white, 14);
  panelHeader(p, 20, 20, W - 40, "Contact information", "Resident contact details");
  text(p, 20, 68, 200, "Phone", 12, 500, css.col.slate500, { lineHeight: 16 });
  text(p, 20, 86, 300, "0927 587 5043", 15, 700, css.col.ink, { lineHeight: 20 });
  text(p, 20, 116, 200, "Email", 12, 500, css.col.slate500, { lineHeight: 16 });
  text(p, 20, 134, 500, "carigjohnmichaelscarig@gmail.com", 15, 700, css.col.ink, { lineHeight: 20 });
  text(p, 20, 164, 200, "Lease status", 12, 500, css.col.slate500, { lineHeight: 16 });
  badge(p, 20, 182, "Active lease", "success");
}

function sLeasesList(container) {
  const a = appShell(container, { active: "leases", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "Leases",
    title: "Leases",
    sub: "Monitor tenant rental agreements and apartment assignments.",
    actions: [{ label: "Create lease", style: "primary", icon: "plus" }],
  });
  y += 24;
  const p = panel(container, X, y, W, 84, css.col.white, 14);
  const sf = frame("search-field", 20, 20, 300, 40, css.col.white, 10, { stroke: css.col.slate200, strokeWeight: 1 });
  icon(sf, 12, 12, "search", css.col.slate400, 16, 2);
  text(sf, 36, 11, 240, "Search tenant or unit…", 13, 400, css.col.slate400, { lineHeight: 18 });
  const chips = ["All", "Active", "Pending", "Terminated"];
  let cx0 = 340;
  chips.forEach((c, i) => { chip(p, cx0, 13, c, i === 0); cx0 += c.length * 7.2 + 34; });
  const rows = LEASES.map((l) => [
    { type: "person", v2: l.tenant.split(" ").map((x) => x[0]).slice(0, 2).join(""), v: l.tenant, sub: "Lease #" + l.id },
    { type: "text", v: l.unit },
    { type: "text", v: l.start },
    { type: "text", v: l.end },
    { type: "badge", v: "Active", tone: "success" },
    { type: "actions" },
  ]);
  simpleTable(container, X, y + 72, W, [
    { t: "TENANT", w: 300 }, { t: "UNIT", w: 100 }, { t: "START", w: 150 }, { t: "END", w: 150 }, { t: "STATUS", w: 130 }, { t: "ACTIONS", w: W - 830, align: "RIGHT" },
  ], rows, { rowH: 64 });
}

function sLeaseForm(container, mode) {
  const isEdit = mode === "edit";
  const a = appShell(container, { active: "leases", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to leases");
  let y = Y + 40;
  pageHead(container, X, y, W, {
    eyebrow: "Leases",
    title: isEdit ? "Edit Lease #3" : "Create lease",
    sub: "Assign a resident to an available apartment.",
  });
  y += 96;
  const p = panel(container, X, y, W, 480, css.col.white, 14);
  const sec = frame("form-section", 20, 20, W - 40, 60, null);
  text(sec, 0, 0, 24, "1", 22, 700, css.col.slate200, { lineHeight: 28 });
  text(sec, 32, 0, 400, "Rental assignment", 16, 700, css.col.ink, { lineHeight: 22 });
  text(sec, 32, 26, 500, "Select a resident and an available unit.", 13, 400, css.col.slate500, { lineHeight: 18 });
  const fields = [
    ["Tenant *", "Select tenant", true], ["Apartment *", "Select available apartment", true],
    ["Start date *", "08 / 01 / 2026", false], ["End date *", "08 / 01 / 2027", false],
    ["Monthly rent", "₱ 8,000.00", false], ["Security deposit", "₱ 0.00", false],
    ["Status", "Active", true],
  ];
  const colW = (W - 88) / 2;
  fields.forEach((f, i) => {
    const fx = X + 20 + (i % 2) * (colW + 24);
    const fy = y + 100 + Math.floor(i / 2) * 96;
    field(container, fx, fy, colW, f[0], f[1], f[2] ? { select: true } : {});
  });
  text(container, X + 20, y + 424, 300, "Only active tenant profiles are shown.", 12, 400, css.col.slate500, { lineHeight: 16 });
  button(container, X + W - 156, y + 410, 136, "Create lease", "primary", "plus");
}

function sLeaseShow(container) {
  const lease = LEASES[2];
  const a = appShell(container, { active: "leases", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to leases");
  let y = Y + 40;
  detailHero(container, X, y, W, {
    icon: "file", iconToneBg: css.col.blue50, iconToneIc: css.col.blue,
    eyebrow: "Tenant", title: "Lease #3", sub: "Juanito Mico · Unit A-001",
    badgeLabel: "Active", badgeTone: "success",
    action: { label: "Edit lease", style: "secondary", icon: "edit" },
  });
  y += 168 + 24;
  valueGrid(container, X, y, W, [
    { label: "Monthly rent", value: lease.rent },
    { label: "Security deposit", value: lease.dep },
    { label: "Start date", value: "Aug 1, 2026", sub: "Move-in date" },
    { label: "End date", value: "Aug 1, 2027", sub: "12 months covered" },
  ]);
  y += 128 + 24;
  const p = panel(container, X, y, W, 130, css.col.white, 14);
  panelHeader(p, 20, 20, W - 40, "Agreement summary", "Lease terms");
  text(p, 20, 60, W - 40, "Lease #3 links Juanito Mico to Unit A-001 at ₱8,000.00 per month. The apartment was occupied on Aug 1, 2026.", 14, 400, css.col.slate600, { lineHeight: 22 });
}

function sBillsList(container) {
  const a = appShell(container, { active: "bills", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "Finance",
    title: "Bills",
    sub: "Monitor rent charges, balances, and due dates across active leases.",
    actions: [{ label: "Record payment", style: "secondary", icon: "card" }],
  });
  const gap = 20;
  const mw = (W - gap * 3) / 4;
  miniStat(container, X, y, mw, "Billed", "₱10,700", "slate", "file");
  miniStat(container, X + mw + gap, y, mw, "Collected", "₱1,000", "green", "check");
  miniStat(container, X + (mw + gap) * 2, y, mw, "Outstanding", "₱9,700", "amber", "receipt");
  miniStat(container, X + (mw + gap) * 3, y, mw, "Overdue", "₱0", "red", "alert");
  y += 64 + 24;
  const p = panel(container, X, y, W, 84, css.col.white, 14);
  const rows = BILLS.map((b) => [
    { type: "text", v: b.month, strong: true },
    { type: "person", v2: "·", v: b.tenant, sub: "" },
    { type: "text", v: b.unit },
    { type: "text", v: b.amount, strong: true },
    { type: "text", v: b.due },
    { type: "badge", v: b.status[0], tone: b.status[1] },
    { type: "actions" },
  ]);
  simpleTable(container, X + 20, y + 72, W - 40, [
    { t: "BILLING MONTH", w: 200 }, { t: "TENANT", w: 260 }, { t: "UNIT", w: 100 }, { t: "AMOUNT", w: 150 }, { t: "DUE DATE", w: 150 }, { t: "STATUS", w: 130 }, { t: "ACTIONS", w: W - 40 - 990, align: "RIGHT" },
  ], rows, { rowH: 64 });
}

function sBillForm(container, mode) {
  const isEdit = mode === "edit";
  const a = appShell(container, { active: "bills", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to bills");
  let y = Y + 40;
  pageHead(container, X, y, W, {
    eyebrow: "Finance",
    title: isEdit ? "Edit Bill #3" : "Add bill",
    sub: "Assign the charge to an active lease and set the billing period.",
  });
  y += 96;
  const p = panel(container, X, y, W, 500, css.col.white, 14);
  const sec = frame("form-section", 20, 20, W - 40, 60, null);
  text(sec, 0, 0, 24, "1", 22, 700, css.col.slate200, { lineHeight: 28 });
  text(sec, 32, 0, 400, "Bill details", 16, 700, css.col.ink, { lineHeight: 22 });
  text(sec, 32, 26, 500, "Assign the charge to an active lease and set the billing period.", 13, 400, css.col.slate500, { lineHeight: 18 });
  const fields = [
    ["Lease / tenant *", "Select lease", true], ["Billing month *", "08 / 2026", false],
    ["Due date *", "08 / 31 / 2026", false], ["Amount", "₱ 8,000.00", false],
    ["Late fee", "₱ 500.00", false], ["Status", "Unpaid", true],
  ];
  const colW = (W - 88) / 2;
  fields.forEach((f, i) => {
    const fx = X + 20 + (i % 2) * (colW + 24);
    const fy = y + 100 + Math.floor(i / 2) * 96;
    field(container, fx, fy, colW, f[0], f[1], f[2] ? { select: true } : {});
  });
  text(container, X + 20, y + 388, 400, "Description", 13, 600, css.col.slate700, { lineHeight: 18 });
  const ta = frame("textarea", X + 20, y + 412, W - 40, 84, css.col.white, 8, { stroke: css.col.slate300, strokeWeight: 1 });
  text(ta, 12, 12, ta.width - 24, "Monthly Payment", 14, 400, css.col.slate400, { lineHeight: 18 });
  button(container, X + W - 130, y + 410, 110, "Create bill", "primary", "plus");
}

function sBillShow(container) {
  const b = BILLS[2];
  const a = appShell(container, { active: "bills", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to bills");
  let y = Y + 40;
  detailHero(container, X, y, W, {
    icon: "receipt", iconToneBg: css.col.amber50, iconToneIc: css.col.amber,
    eyebrow: "Billing period", title: b.month, sub: b.tenant + " · Unit " + b.unit + " · Due " + b.due,
    badgeLabel: "Unpaid", badgeTone: "neutral",
    action: { label: "Edit bill", style: "secondary", icon: "edit" },
  });
  y += 168 + 24;
  valueGrid(container, X, y, W, [
    { label: "Bill amount", value: "₱8,000.00" },
    { label: "Late fee", value: "₱500.00" },
    { label: "Total due", value: "₱8,500.00" },
    { label: "Paid so far", value: "₱0.00" },
  ]);
  y += 128 + 24;
  const p = panel(container, X, y, W, 130, css.col.white, 14);
  panelHeader(p, 20, 20, W - 40, "Description", "Payment note");
  text(p, 20, 60, W - 40, "Monthly Payment", 14, 400, css.col.slate600, { lineHeight: 22 });
}

function sPaymentsList(container) {
  const a = appShell(container, { active: "payments", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "Finance",
    title: "Payment history",
    sub: "Review and reconcile all recorded tenant payments.",
    actions: [{ label: "Record payment", style: "primary", icon: "plus" }],
  });
  y += 24;
  const p = panel(container, X, y, W, 84, css.col.white, 14);
  const sf = frame("search-field", 20, 20, 320, 40, css.col.white, 10, { stroke: css.col.slate200, strokeWeight: 1 });
  icon(sf, 12, 12, "search", css.col.slate400, 16, 2);
  text(sf, 36, 11, 240, "Search tenant, unit, or reference…", 13, 400, css.col.slate400, { lineHeight: 18 });
  const mf = frame("method-filter", 360, 13, 150, 40, css.col.white, 10, { stroke: css.col.slate200, strokeWeight: 1 });
  text(mf, 12, 12, 110, "All methods", 13, 500, css.col.slate700, { lineHeight: 18 });
  icon(mf, 122, 12, "chev", css.col.slate500, 16, 2);
  const meths = ["Cash", "GCash", "Bank transfer", "Online payment"];
  let cx0 = 530;
  meths.forEach((c) => { chip(p, cx0, 13, c, false); cx0 += c.length * 7.2 + 34; });
  const rows = PAYMENTS.map((pm) => [
    { type: "person", v2: "JC", v: pm.tenant, sub: "Bill #" + pm.bill },
    { type: "text", v: pm.unit },
    { type: "text", v: pm.date },
    { type: "tag", v: pm.method, tone: pm.tag },
    { type: "text", v: "₱1,000.00", strong: true, sub: "Paid", subColor: css.col.green },
    { type: "actions", actions: ["eye", "download"] },
  ]);
  simpleTable(container, X, y + 72, W, [
    { t: "TENANT", w: 280 }, { t: "UNIT", w: 100 }, { t: "DATE", w: 140 }, { t: "METHOD", w: 170 }, { t: "AMOUNT", w: 180, align: "RIGHT" }, { t: "ACTIONS", w: W - 870, align: "RIGHT" },
  ], rows, { rowH: 64 });
}

function sPaymentCreate(container) {
  const a = appShell(container, { active: "payments", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to bills");
  let y = Y + 40;
  pageHead(container, X, y, W, {
    eyebrow: "Payments",
    title: "Record payment",
    sub: "Apply a tenant payment to an outstanding bill.",
  });
  y += 96;
  const leftW = 700;
  const p = panel(container, X, y, leftW, 460, css.col.white, 14);
  const sec = frame("form-section", 20, 20, leftW - 40, 60, null);
  text(sec, 0, 0, 24, "1", 22, 700, css.col.slate200, { lineHeight: 28 });
  text(sec, 32, 0, 400, "Payment information", 16, 700, css.col.ink, { lineHeight: 22 });
  text(sec, 32, 26, 400, "Select a bill and enter the transaction details.", 13, 400, css.col.slate500, { lineHeight: 18 });
  const fields = [
    ["Bill *", "Select an outstanding bill", true], ["Tenant *", "Juanito Mico", true],
    ["Method *", "Cash", true], ["Amount paid", "₱ 8,500.00", false],
    ["Payment date *", "09 / 19 / 2026", false], ["Reference", "TST-001", false],
  ];
  const colW = (leftW - 88) / 2;
  fields.forEach((f, i) => {
    const fx = X + 20 + (i % 2) * (colW + 24);
    const fy = y + 100 + Math.floor(i / 2) * 96;
    field(container, fx, fy, colW, f[0], f[1], f[2] ? { select: true } : {});
  });
  text(container, X + 20, y + 388, 400, "Notes", 13, 600, css.col.slate700, { lineHeight: 18 });
  const ta = frame("textarea", X + 20, y + 412, leftW - 40, 48, css.col.white, 8, { stroke: css.col.slate300, strokeWeight: 1 });
  text(ta, 12, 12, ta.width - 24, "", 14, 400, css.col.slate400, { lineHeight: 18 });
  button(container, X + leftW - 140, y + 412, 120, "Record payment", "primary", "card");
  // bill summary panel
  const rp = panel(container, X + leftW + 24, y, W - leftW - 24, 460, css.col.white, 14);
  panelHeader(rp, 20, 20, rp.width - 40, "Bill summary", "Selected outstanding bill");
  summaryIcon(rp, 20, 78, "amber", "receipt", 48);
  text(rp, 80, 82, 200, "Bill #3", 16, 700, css.col.ink, { lineHeight: 22 });
  text(rp, 80, 106, 200, "August 2026", 12, 400, css.col.slate500, { lineHeight: 16 });
  const lines = [
    ["Tenant", "Juanito Mico"], ["Unit", "A-001"], ["Total", "₱8,500.00"], ["Balance", "₱8,500.00"],
  ];
  lines.forEach((l, i) => {
    const ly = 156 + i * 40;
    rect(rp, 20, ly - 12, rp.width - 40, 1, css.col.slate100);
    text(rp, 20, ly, 160, l[0], 13, 400, css.col.slate500, { lineHeight: 18 });
    text(rp, rp.width - 120, ly, 100, l[1], 13, i >= 2 ? 700 : 500, i >= 2 ? css.col.ink : css.col.slate700, { align: "RIGHT", lineHeight: 18 });
  });
  badge(rp, 20, 330, "Payment needed", "warning");
}

function sPaymentShow(container) {
  const a = appShell(container, { active: "payments", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to payments");
  let y = Y + 40;
  pageHead(container, X, y, W, {
    eyebrow: "Reference #000001",
    title: "Payment details",
    sub: "Recorded on Jul 29, 2026.",
    actions: [{ label: "Download receipt", style: "secondary", icon: "download" }],
  });
  y += 96;
  const pw = Math.min(W, 560);
  const p = panel(container, X + (W - pw) / 2, y, pw, 520, css.col.white, 14);
  // seal
  const seal = frame("success-seal", (pw - 96) / 2, 32, 96, 96, css.col.green50, 48);
  icon(seal, 30, 30, "check", css.col.green, 36, 3);
  text(p, 0, 140, pw, "Amount paid", 12, 500, css.col.slate500, { align: "CENTER", lineHeight: 16 });
  text(p, 0, 160, pw, "₱1,000.00", 32, 700, css.col.ink, { align: "CENTER", lineHeight: 42 });
  text(p, 0, 208, pw, "Paid", 13, 600, css.col.green, { align: "CENTER", lineHeight: 18 });
  rect(p, 40, 244, pw - 80, 1, css.col.slate100);
  const rows = [
    ["Bill", "Bill #1"], ["Tenant", "John Michael Carig"], ["Unit", "A-101"],
    ["Billing period", "August 2026"], ["Method", "Cash"], ["Payment date", "Jul 29, 2026"], ["Reference", "—"],
  ];
  rows.forEach((r, i) => {
    const ly = 260 + i * 30;
    text(p, 40, ly, 200, r[0], 13, 400, css.col.slate500, { lineHeight: 18 });
    text(p, 200, ly, pw - 240, r[1], 13, 600, css.col.slate700, { align: "RIGHT", lineHeight: 18 });
  });
  rect(p, 40, 470, pw - 80, 1, css.col.slate100);
  const totals = [["Bill total", "₱1,000.00", 400], ["Paid", "₱1,000.00", 400], ["Balance", "₱0.00", 700]];
  totals.forEach((t, i) => {
    const ly = 486 + i * 0;
    text(p, 40, 486 + i * 30, 200, t[0], 13, i === 2 ? 700 : 400, i === 2 ? css.col.ink : css.col.slate500, { lineHeight: 18 });
    text(p, 200, 486 + i * 30, pw - 240, t[1], 13, t[2], css.col.ink, { align: "RIGHT", lineHeight: 18 });
  });
  text(p, 0, 582, pw, "StayTrack Apartment Management · support@staytrack.ph", 12, 400, css.col.slate400, { align: "CENTER", lineHeight: 16 });
}

function sMaintenanceList(container, withData) {
  const a = appShell(container, { active: "maintenance", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "Maintenance",
    title: "Maintenance requests",
    sub: "Track repair and upkeep requests across the building.",
    actions: [{ label: "New request", style: "primary", icon: "plus" }],
  });
  const gap = 20;
  const mw = (W - gap * 2) / 3;
  const openCount = withData ? 2 : 0;
  const doneCount = withData ? 1 : 0;
  const allCount = withData ? 3 : 0;
  miniStat(container, X, y, mw, "Open requests", String(openCount), "slate", "wrench");
  miniStat(container, X + mw + gap, y, mw, "Completed", String(doneCount), "green", "check");
  miniStat(container, X + (mw + gap) * 2, y, mw, "All time", String(allCount), "blue", "file");
  y += 64 + 24;
  const p = panel(container, X, y, W, 84, css.col.white, 14);
  const chips = ["Pending", "In progress", "Completed", "Cancelled"];
  let cx0 = 20;
  chips.forEach((c, i) => { chip(p, cx0, 13, c, false); cx0 += c.length * 7.2 + 34; });
  const rows = [];
  if (withData) {
    MREQ.forEach((m) => {
      rows.push([
        { type: "text", v: m.title, strong: true },
        { type: "text", v: m.unit },
        { type: "text", v: m.category, muted: true },
        { type: "text", v: m.priority, color: PRIORITY_COLORS[m.priority] },
        { type: "badge", v: m.status[0], tone: m.status[1] },
        { type: "text", v: m.date, muted: true },
        { type: "actions", icon: "eye" },
      ]);
    });
  } else {
    rows.push([
      { type: "empty", v: "No maintenance requests yet." },
      { type: "empty", v: "" }, { type: "empty", v: "" }, { type: "empty", v: "" }, { type: "empty", v: "" }, { type: "empty", v: "" },
    ]);
  }
  simpleTable(container, X, y + 72, W, [
    { t: "REQUEST", w: 260 }, { t: "UNIT", w: 100 }, { t: "CATEGORY", w: 140 }, { t: "PRIORITY", w: 120 }, { t: "STATUS", w: 140 }, { t: "DATE", w: 140 }, { t: "ACTIONS", w: W - 900, align: "RIGHT" },
  ], rows, { rowH: withData ? 72 : 120 });
}

function sMaintenanceCreate(container) {
  const a = appShell(container, { active: "maintenance", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to requests");
  let y = Y + 40;
  pageHead(container, X, y, W, {
    eyebrow: "Maintenance",
    title: "Submit a request",
    sub: "Tell us what needs attention and we will get on it.",
  });
  y += 96;
  const p = panel(container, X, y, W, 470, css.col.white, 14);
  const sec = frame("form-section", 20, 20, W - 40, 60, null);
  text(sec, 0, 0, 24, "1", 22, 700, css.col.slate200, { lineHeight: 28 });
  text(sec, 32, 0, 400, "About the request", 16, 700, css.col.ink, { lineHeight: 22 });
  text(sec, 32, 26, 500, "Describe the issue and where it is happening.", 13, 400, css.col.slate500, { lineHeight: 18 });
  const fields = [
    ["Tenant *", "Select tenant", true], ["Category *", "Plumbing", true],
    ["Priority", "High", true], ["Title *", "Leaking faucet in bathroom", false],
  ];
  const colW = (W - 88) / 2;
  fields.forEach((f, i) => {
    const fx = X + 20 + (i % 2) * (colW + 24);
    const fy = y + 100 + Math.floor(i / 2) * 96;
    field(container, fx, fy, colW, f[0], f[1], f[2] ? { select: true } : {});
  });
  text(container, X + 20, y + 292, 400, "Description", 13, 600, css.col.slate700, { lineHeight: 18 });
  const ta = frame("textarea", X + 20, y + 316, W - 40, 96, css.col.white, 8, { stroke: css.col.slate300, strokeWeight: 1 });
  text(ta, 12, 12, ta.width - 24, "The bathroom faucet in Unit A-101 is leaking water continuously.", 14, 400, css.col.slate400, { lineHeight: 18 });
  button(container, X + W - 140, y + 428, 120, "Submit request", "primary", "plus");
}

function sMaintenanceShow(container) {
  const a = appShell(container, { active: "maintenance", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to requests");
  let y = Y + 40;
  detailHero(container, X, y, W, {
    icon: "wrench", iconToneBg: css.col.amber50, iconToneIc: css.col.amber,
    eyebrow: "Request #1", title: "Leaking faucet in bathroom", sub: "Plumbing · Unit A-101",
    badgeLabel: "Pending", badgeTone: "warning",
    action: { label: "Update request", style: "secondary", icon: "edit" },
  });
  y += 168 + 24;
  valueGrid(container, X, y, W, [
    { label: "Submitted on", value: "Aug 10, 2026" }, { label: "Category", value: "Plumbing" },
    { label: "Priority", value: "High" }, { label: "Status", value: "Pending" },
  ]);
  y += 128 + 24;
  const p = panel(container, X, y, W, 150, css.col.white, 14);
  panelHeader(p, 20, 20, W - 40, "Description", "What needs attention");
  text(p, 20, 60, W - 40, "The bathroom faucet in Unit A-101 is leaking water continuously and needs urgent repair.", 14, 400, css.col.slate600, { lineHeight: 22 });
}

function sProfile(container) {
  const a = appShell(container, { active: "profile", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "Account",
    title: "Profile settings",
    sub: "Manage your personal information, password, and account access.",
  });
  y += 24;
  const navW = 280;
  const nav = panel(container, X, y, navW, 320, css.col.white, 14);
  const av = frame("profile-avatar", (navW - 88) / 2, 24, 88, 88, css.col.slate200, 44);
  const it = text(av, 0, 24, 88, "SA", 30, 700, css.col.slate700, { align: "CENTER" });
  text(nav, 0, 124, navW, "StayTrack Admin", 17, 700, css.col.ink, { align: "CENTER", lineHeight: 24 });
  text(nav, 0, 150, navW, "Administrator", 13, 400, css.col.slate500, { align: "CENTER", lineHeight: 18 });
  const links = [
    ["Personal information", "user", true],
    ["Password & security", "file", false],
    ["Danger zone", "alert", false],
  ];
  links.forEach((l, i) => {
    const ly = 188 + i * 44;
    const row = frame("profile-link", 16, ly, navW - 32, 36, l[2] ? css.col.slate50 : null, 8);
    icon(row, 10, 10, l[1], l[2] ? css.col.slate900 : css.col.slate400, 16, 2);
    text(row, 34, 10, 180, l[0], 13, l[2] ? 700 : 500, l[2] ? css.col.ink : css.col.slate600, { lineHeight: 18 });
  });
  const p = panel(container, X + navW + 24, y, W - navW - 24, 320, css.col.white, 14);
  panelHeader(p, 24, 24, p.width - 48, "Personal information", "Update your name and email address.");
  field(p, 24, 76, (p.width - 72) / 2, "Name", "StayTrack Admin", {});
  field(p, 24 + (p.width - 72) / 2 + 24, 76, (p.width - 72) / 2, "Email address", "admin@staytrack.test", {});
  button(p, p.width - 180, 152, 156, "Update profile", "primary");
}

// ---------------------------------------------------------------------
//  Section 03 — Tenant screens
// ---------------------------------------------------------------------
function sTenantDashboard(container, noLease) {
  const lease = noLease ? null : LEASES[1];
  const a = appShell(container, { role: "tenant", active: "dashboard", user: "Jm Carig", email: "carigjohnmichaelscarig@gmail.com", roleLabel: "Tenant", initials: "JC" });
  const X = a.x, Y = a.y, W = a.w;
  pageHead(container, X, Y, W, {
    eyebrow: "Tenant portal",
    title: "Welcome, Jm Carig",
    sub: "Your apartment, lease, bills, and payment history in one place.",
  });
  let y = Y + 86;
  if (noLease) {
    const nf = panel(container, X, y, W, 96, css.col.white, 14);
    const sec = frame("form-section", 24, 22, W - 48, 56, null);
    summaryIcon(sec, 0, 4, "amber", "alert", 40);
    text(sec, 56, 2, 400, "No active lease", 16, 700, css.col.ink, { lineHeight: 22 });
    text(sec, 56, 28, 500, "Your account is not linked to an active lease yet. Please contact the property office at support@staytrack.ph.", 13, 400, css.col.slate500, { lineHeight: 18 });
    y += 96 + 24;
  }
  const gap = 20;
  const mw = (W - gap * 2) / 3;
  // summary cards
  const s1 = panel(container, X, y, mw, 128, css.col.white, 14);
  text(s1, 20, 18, 150, "My apartment", 13, 600, css.col.slate600, { lineHeight: 18 });
  summaryIcon(s1, mw - 60, 14, "slate", "home", 40);
  text(s1, 20, 46, mw - 40, lease ? "Unit A-101" : "No apartment", 20, 700, css.col.ink, { lineHeight: 26 });
  text(s1, 20, 74, mw - 40, lease ? "1 Bedroom" : "No unit assigned yet", 13, 400, css.col.slate500, { lineHeight: 18 });
  badge(s1, 20, 96, lease ? "Active lease" : "No lease", lease ? "success" : "neutral");
  const s2 = panel(container, X + mw + gap, y, mw, 128, css.col.white, 14);
  text(s2, 20, 18, 150, "Monthly rent", 13, 600, css.col.slate600, { lineHeight: 18 });
  summaryIcon(s2, mw - 60, 14, "blue", "wallet", 40);
  text(s2, 20, 46, mw - 40, lease ? "₱800.00" : "₱0.00", 20, 700, css.col.ink, { lineHeight: 26 });
  text(s2, 20, 74, mw - 40, "Current lease rate", 13, 400, css.col.slate500, { lineHeight: 18 });
  text(s2, 20, 96, mw - 40, lease ? "Active since Jul 29, 2026" : "No active lease", 12, 500, css.col.slate500, { lineHeight: 16 });
  const s3 = panel(container, X + (mw + gap) * 2, y, mw, 128, css.col.white, 14);
  text(s3, 20, 18, 150, "Outstanding balance", 13, 600, css.col.slate600, { lineHeight: 18 });
  summaryIcon(s3, mw - 60, 14, lease ? "amber" : "green", "check", 40);
  text(s3, 20, 46, mw - 40, lease ? "₱1,200.00" : "₱0.00", 20, 700, css.col.ink, { lineHeight: 26 });
  text(s3, 20, 74, mw - 40, lease ? "1 bill pending" : "All bills settled", 13, 400, css.col.slate500, { lineHeight: 18 });
  text(s3, 20, 96, mw - 40, lease ? "Payment needed" : "You are up to date", 12, 600, lease ? css.col.amber : css.col.green, { lineHeight: 16 });
  y += 128 + 24;
  // lease card
  const lp = panel(container, X, y, W, 250, css.col.white, 14);
  const leaseTitle = lease ? (lease.status || "Active") : "No lease";
  panelHeader(lp, 20, 20, W - 40, "My lease", "Current rental agreement");
  badge(lp, W - 120, 20, "Active", "success");
  const cols = [
    ["Apartment", lease ? "Unit A-101" : "—", lease ? "1 Bedroom" : "—"],
    ["Start date", lease ? "Jul 29, 2026" : "—", "Move-in date"],
    ["End date", lease ? "Aug 29, 2026" : "No end date", lease ? "0 months covered" : "—"],
    ["Security deposit", lease ? "₱1,000.00" : "—", "Refundable"],
  ];
  const cw = (W - 88) / 4;
  cols.forEach((c, i) => {
    const cx = X + 20 + i * (cw + 8);
    text(container, cx, y + 64, cw, c[0], 12, 500, css.col.slate500, { lineHeight: 16 });
    text(container, cx, y + 84, cw, c[1], 15, 700, css.col.ink, { lineHeight: 20 });
    text(container, cx, y + 106, cw, c[2], 12, 400, css.col.slate500, { lineHeight: 16 });
  });
  // progress
  text(container, X + 20, y + 150, 200, "Lease progress", 12, 500, css.col.slate500, { lineHeight: 16 });
  text(container, X + W - 120, y + 150, 100, "0%", 13, 700, css.col.ink, { align: "RIGHT", lineHeight: 16 });
  const track = frame("progress-track", X + 20, y + 174, W - 40, 8, css.col.slate200, 4);
  const fill = frame("progress-fill", X + 20, y + 174, 0, 8, css.col.ink, 4);
  y += 250 + 24;
  // bills + payments grid
  const half = (W - gap) / 2;
  const b1 = panel(container, X, y, half, 260, css.col.white, 14);
  panelHeader(b1, 20, 20, half - 40, "My bills", "Recent billing records");
  badge(b1, half - 100, 20, lease ? "1 open" : "No balance", lease ? "warning" : "success");
  if (lease) {
    const row = frame("bill-row", 20, 76, half - 40, 64, null);
    summaryIcon(row, 0, 12, "slate", "receipt", 40);
    text(row, 54, 14, 220, "July 2026", 14, 700, css.col.ink, { lineHeight: 18 });
    text(row, 54, 34, 220, "Due Aug 1", 12, 400, css.col.slate500, { lineHeight: 16 });
    text(row, row.width - 140, 14, 140, "₱1,200.00", 14, 700, css.col.ink, { align: "RIGHT", lineHeight: 18 });
    text(row, row.width - 140, 34, 140, "Unpaid", 12, 500, css.col.slate500, { align: "RIGHT", lineHeight: 16 });
  } else {
    const row = frame("bill-row", 20, 76, half - 40, 64, null);
    summaryIcon(row, 0, 12, "slate", "receipt", 40);
    text(row, 54, 14, 220, "No bills yet", 14, 700, css.col.ink, { lineHeight: 18 });
    text(row, 54, 34, 220, "Billing records will appear here", 12, 400, css.col.slate500, { lineHeight: 16 });
  }
  const b2 = panel(container, X + half + gap, y, half, 260, css.col.white, 14);
  panelHeader(b2, 20, 20, half - 40, "Payment history", "Your latest transactions");
  const trow = frame("timeline-row", 20, 76, half - 40, 64, null);
  ellipse(trow, 10, 22, 10, 10, css.col.white, { stroke: css.col.slate400, strokeWeight: 1.5 });
  text(trow, 36, 12, 200, "No payments yet", 13, 600, css.col.slate700, { lineHeight: 18 });
  text(trow, 36, 32, 240, "Your transactions will appear here", 12, 400, css.col.slate500, { lineHeight: 16 });
  y += 260 + 24;
  // help card
  const hf = frame("tenant-help", X, y, W, 92, css.col.blue50, 14);
  summaryIcon(hf, 24, 26, "blue", "user", 40);
  text(hf, 80, 20, 500, "Need help with your account?", 14, 700, css.col.ink, { lineHeight: 20 });
  text(hf, 80, 42, 560, "Contact the property office at +63 2 8123 4567 or support@staytrack.ph.", 13, 400, css.col.slate600, { lineHeight: 18 });
  button(hf, hf.width - 172, 24, 148, "Contact support", "secondary", null, 44);
}

function sTenantProfile(container) {
  const a = appShell(container, { role: "tenant", active: "profile", user: "Jm Carig", email: "carigjohnmichaelscarig@gmail.com", roleLabel: "Tenant", initials: "JC" });
  const X = a.x, Y = a.y, W = a.w;
  let y = pageHead(container, X, Y, W, {
    eyebrow: "Account",
    title: "Profile settings",
    sub: "Manage your personal information, password, and account access.",
  });
  y += 24;
  const navW = 280;
  const nav = panel(container, X, y, navW, 320, css.col.white, 14);
  const av = frame("profile-avatar", (navW - 88) / 2, 24, 88, 88, css.col.slate200, 44);
  text(av, 0, 24, 88, "JC", 30, 700, css.col.slate700, { align: "CENTER" });
  text(nav, 0, 124, navW, "Jm Carig", 17, 700, css.col.ink, { align: "CENTER", lineHeight: 24 });
  text(nav, 0, 150, navW, "Tenant", 13, 400, css.col.slate500, { align: "CENTER", lineHeight: 18 });
  const links = [
    ["Personal information", "user", true],
    ["Password & security", "file", false],
    ["Danger zone", "alert", false],
  ];
  links.forEach((l, i) => {
    const ly = 188 + i * 44;
    const row = frame("profile-link", 16, ly, navW - 32, 36, l[2] ? css.col.slate50 : null, 8);
    icon(row, 10, 10, l[1], l[2] ? css.col.slate900 : css.col.slate400, 16, 2);
    text(row, 34, 10, 180, l[0], 13, l[2] ? 700 : 500, l[2] ? css.col.ink : css.col.slate600, { lineHeight: 18 });
  });
  const p = panel(container, X + navW + 24, y, W - navW - 24, 320, css.col.white, 14);
  panelHeader(p, 24, 24, p.width - 48, "Personal information", "Update your name and email address.");
  field(p, 24, 76, (p.width - 72) / 2, "Name", "Jm Carig", {});
  field(p, 24 + (p.width - 72) / 2 + 24, 76, (p.width - 72) / 2, "Email address", "carigjohnmichaelscarig@gmail.com", {});
  button(p, p.width - 180, 152, 156, "Update profile", "primary");
}

// ---------------------------------------------------------------------
//  Section 04 — Components (design system reference)
// ---------------------------------------------------------------------
function buildS4(page) {
  const c = place(page, 1280, 1720, "04 · Design tokens & components");
  const X = 32, Y = 32;
  text(c, X, Y, 200, "StayTrack Design System", 24, 700, css.col.ink, { lineHeight: 32 });
  text(c, X, Y + 34, 800, "Fully editable components — rebuild any screen by composing these.", 13, 400, css.col.slate500, { lineHeight: 18 });
  // palette
  const palY = 90;
  text(c, X, palY, 300, "Color palette", 14, 700, css.col.ink, { lineHeight: 20 });
  const groups = [
    ["Slate", ["#f8fafc", "#f1f5f9", "#e2e8f0", "#cbd5e1", "#94a3b8", "#64748b", "#475569", "#334155", "#1e293b", "#0f172a"]],
    ["Blue", ["#eff6ff", "#dbeafe", "#bfdbfe", "#2563eb", "#1d4ed8", "#1e3a8a"]],
    ["Emerald", ["#ecfdf5", "#d1fae5", "#059669", "#047857", "#065f46"]],
    ["Amber", ["#fffbeb", "#fde68a", "#d97706", "#b45309"]],
    ["Red", ["#fef2f2", "#fecaca", "#ef4444", "#dc2626", "#b91c1c"]],
  ];
  let gx = X;
  groups.forEach((g) => {
    rect(c, gx, palY + 26, 12, 12, css.col.slate400);
    text(c, gx + 18, palY + 22, 200, g[0], 12, 700, css.col.ink, { lineHeight: 20 });
    g[1].forEach((hx, i) => {
      const sw = 38;
      const sx = gx + Math.floor(i / 5) * (sw + 6) * 5 + (i % 5) * (sw + 6) + 18;
      const sy = palY + 52 + Math.floor(i / 5) * 46;
      rect(c, sx, sy, sw, 28, hex2rgb(hx), 4, { stroke: css.col.slate200, strokeWeight: 1, name: hx });
      text(c, sx, sy + 30, sw, hx, 8, 400, css.col.slate500, { lineHeight: 10, align: "CENTER" });
    });
    gx += 270;
  });
  // typography
  let ty = 340;
  text(c, X, ty, 300, "Typography — Figtree", 14, 700, css.col.ink, { lineHeight: 20 });
  const typeSamples = [
    ["Display / H1", 32, 700, "Run your apartments with clarity."],
    ["H2", 24, 700, "Welcome back, StayTrack Admin"],
    ["H3", 18, 700, "Recent payments"],
    ["Body", 14, 400, "Track units, tenants, leases, bills, and payments from one workspace."],
    ["Small / Muted", 12, 400, "Secure property operations for Metro Manila teams"],
  ];
  typeSamples.forEach((s, i) => {
    text(c, X, ty + 34 + i * 46, 300, s[0], 11, 600, css.col.slate500, { ls: 0.5, lineHeight: 14 });
    text(c, X + 320, ty + 30 + i * 46, 600, s[3], s[1], s[2], css.col.ink, { lineHeight: s[1] + 10 });
  });
  // buttons
  let by = ty + 34 + 5 * 46 + 30;
  text(c, X, by, 300, "Buttons", 14, 700, css.col.ink, { lineHeight: 20 });
  button(c, X, by + 30, 140, "Primary", "primary");
  button(c, X + 156, by + 30, 140, "Secondary", "secondary");
  button(c, X + 312, by + 30, 140, "Danger", "danger");
  button(c, X + 468, by + 30, 160, "Primary + icon", "primary", "arrow");
  button(c, X + 644, by + 30, 160, "Large", "primary", null, 52);
  button(c, X + 820, by + 30, 160, "Ghost", "ghost");
  // badges
  let zy = by + 110;
  text(c, X, zy, 300, "Badges", 14, 700, css.col.ink, { lineHeight: 20 });
  badge(c, X, zy + 30, "Available", "success");
  badge(c, X + 110, zy + 30, "In progress", "info");
  badge(c, X + 230, zy + 30, "Pending", "warning");
  badge(c, X + 350, zy + 30, "Overdue", "danger");
  badge(c, X + 470, zy + 30, "Inactive", "neutral");
  badge(c, X + 590, zy + 24, "Active lease", "success", true);
  // tags + chips
  let ty2 = zy + 110;
  text(c, X, ty2, 300, "Tags & chips", 14, 700, css.col.ink, { lineHeight: 20 });
  tag(c, X, ty2 + 30, "Cash", "green");
  tag(c, X + 100, ty2 + 30, "GCash", "blue");
  tag(c, X + 200, ty2 + 30, "Bank transfer", "slate");
  chip(c, X + 330, ty2 + 30, "All", true);
  chip(c, X + 400, ty2 + 30, "Available", false);
  chip(c, X + 510, ty2 + 30, "Occupied", false);
  // form elements
  let fy = ty2 + 130;
  text(c, X, fy, 300, "Form elements", 14, 700, css.col.ink, { lineHeight: 20 });
  field(c, X, fy + 30, 320, "Label", "Placeholder text", {});
  field(c, X, fy + 104, 320, "Select", "Select an option", { select: true });
  field(c, X + 350, fy + 30, 320, "Password", "••••••••••", { eye: true });
  field(c, X + 350, fy + 104, 320, "With error", "Invalid input", { error: "This field is invalid." });
  checkRow(c, X, fy + 180, "Keep me signed in on this device");
  avatar(c, X + 350, fy + 178, 32, "JC", {});
  avatar(c, X + 396, fy + 178, 32, "SA", { bg: css.col.slate300 });
  // panels / metrics
  let gy = fy + 250;
  text(c, X, gy, 300, "Cards & metrics", 14, 700, css.col.ink, { lineHeight: 20 });
  metricCard(c, X, gy + 30, 240, "Total apartments", "building", "slate", "4", [{ type: "text", value: "2 occupied", color: css.col.green, weight: 600 }]);
  miniStat(c, X + 260, gy + 30, 200, "Available", "2", "green", "check");
  summaryIcon(c, X + 480, gy + 30, "amber", "receipt");
  badge(c, X + 540, gy + 40, "Status", "info");
  // empty state
  let ey = gy + 180;
  text(c, X, ey, 300, "Empty states & feedback", 14, 700, css.col.ink, { lineHeight: 20 });
  const es = frame("empty-state", X, ey + 30, 360, 120, css.col.white, 14, { stroke: css.col.slate200, strokeWeight: 1 });
  summaryIcon(es, (360 - 40) / 2, 20, "slate", "file", 40);
  text(es, 0, 72, 360, "No records yet", 15, 700, css.col.ink, { align: "CENTER", lineHeight: 22 });
  text(es, 0, 96, 360, "Records will appear here once added.", 13, 400, css.col.slate500, { align: "CENTER", lineHeight: 18 });
  toast(c, X + 390, ey + 30, "Your changes were saved.");
  deleteModal(c, X + 790, ey + 20, "Apartment A-101");
  notificationPanel(c, X + 790, ey + 300);
}

// ---------------------------------------------------------------------
//  Section 05 — Prototype flows
// ---------------------------------------------------------------------
function flowNode(parent, x, y, title, url, primary, wide) {
  const f = frame("flow-node", x, y, wide ? 260 : 210, 112, css.col.white, 12, { stroke: primary ? css.col.ink : css.col.slate200, strokeWeight: primary ? 2 : 1, shadowSm: true });
  const dot = ellipse(f, 16, 16, 8, 8, primary ? css.col.ink : css.col.slate300);
  text(f, 32, 12, f.width - 44, title, 14, 700, css.col.ink, { lineHeight: 19 });
  text(f, 16, 40, f.width - 32, url, 11, 400, css.col.slate500, { lineHeight: 15 });
  text(f, 16, f.width <= 0 ? 0 : 62, f.width - 32, "→ editable Figma frame", 10, 500, primary ? css.col.green : css.col.slate400, { lineHeight: 14 });
  return f;
}

function flowArrow(parent, x1, y1, x2, y2, label) {
  line(parent, x1, y1, x2, y2, css.col.slate400, 1.5);
  const a = Math.atan2(y2 - y1, x2 - x1) * 180 / Math.PI;
  const h = frame("arrow-head", x2 - 4, y2 - 6, 12, 12, null);
  h.rotation = a;
  const tr = frame("arrow-tri", 0, 0, 12, 12, null);
  tr.fills = [];
  // simple triangle using path-free shapes: two lines
  line(tr, 1, 1, 11, 6); line(tr, 1, 11, 11, 6);
  h.appendChild(tr);
  parent.appendChild(h);
  if (label) text(parent, (x1 + x2) / 2 - 30, (y1 + y2) / 2 - 10, 120, label, 10, 500, css.col.slate500, { align: "CENTER", lineHeight: 12 });
}

function flowGroup(page, title, h, build) {
  const c = place(page, 1560, h, title);
  text(c, 32, 28, 500, title, 18, 700, css.col.ink, { lineHeight: 26 });
  build(c);
}

function buildS5(page) {
  flowGroup(page, "05 · Prototype flow — Landing & authentication", 560, (c) => {
    flowNode(c, 40, 70, "Landing", "/", false, true);
    flowNode(c, 380, 70, "Login", "/login", true, true);
    flowNode(c, 720, 70, "Admin dashboard", "/dashboard", false, true);
    flowArrow(c, 300, 126, 378, 126, "Sign in");
    flowArrow(c, 640, 126, 718, 126, "→");
    flowNode(c, 40, 260, "Register", "/register", false, true);
    flowNode(c, 380, 260, "Verify email", "/verify-email", false, true);
    flowNode(c, 720, 260, "Tenant dashboard", "/dashboard", false, true);
    flowArrow(c, 300, 316, 378, 316, "Create account");
    flowArrow(c, 640, 316, 718, 316, "→");
    flowNode(c, 40, 450, "Forgot password", "/forgot-password", false, true);
    flowNode(c, 380, 450, "Reset password", "/reset-password/{token}", false, true);
    flowNode(c, 720, 450, "Login", "/login", false, true);
    flowArrow(c, 300, 506, 378, 506, "Email link");
    flowArrow(c, 640, 506, 718, 506, "New password");
  });
  flowGroup(page, "05 · Prototype flow — Admin (StayTrack Workspace)", 940, (c) => {
    flowNode(c, 40, 70, "Dashboard", "/dashboard", true, true);
    flowNode(c, 340, 70, "Apartments", "/apartments", false, false);
    flowNode(c, 590, 70, "Apartment create", "/apartments/create", false, false);
    flowNode(c, 840, 70, "Apartment A-101", "/apartments/1", false, false);
    flowArrow(c, 300, 90, 338, 90, "");
    flowArrow(c, 500, 90, 588, 90, "");
    flowArrow(c, 750, 90, 838, 90, "");
    flowNode(c, 340, 270, "Tenants", "/tenants", false, false);
    flowNode(c, 590, 270, "Tenant create", "/tenants/create", false, false);
    flowNode(c, 840, 270, "Tenant Jm Carig", "/tenants/2", false, false);
    flowArrow(c, 340, 388, 340, 352, ""); flowArrow(c, 340, 90, 340, 268, "");
    flowArrow(c, 500, 290, 588, 290, "");
    flowArrow(c, 750, 290, 838, 290, "");
    flowNode(c, 340, 470, "Leases", "/leases", false, false);
    flowNode(c, 590, 470, "Lease #3", "/leases/3", false, false);
    flowNode(c, 840, 470, "Bills", "/bills", false, false);
    flowArrow(c, 500, 490, 588, 490, "");
    flowArrow(c, 750, 490, 838, 490, "");
    flowNode(c, 40, 470, "Billing overview", "→ bills", false, false);
    flowNode(c, 40, 670, "Payments", "/payments", true, false);
    flowNode(c, 290, 670, "Record payment", "/payments/create", false, false);
    flowNode(c, 540, 670, "Payment detail", "/payments/1", false, false);
    flowArrow(c, 250, 690, 288, 690, "");
    flowArrow(c, 450, 690, 538, 690, "");
    flowNode(c, 840, 670, "Maintenance", "/maintenance", false, false);
    flowNode(c, 1090, 670, "New request", "/maintenance/create", false, false);
    flowArrow(c, 1000, 690, 1088, 690, "");
    flowNode(c, 40, 850, "Profile", "/profile", false, false);
    flowArrow(c, 40, 782, 40, 848, "");
  });
  flowGroup(page, "05 · Prototype flow — Tenant", 620, (c) => {
    flowNode(c, 40, 70, "My dashboard", "/dashboard", true, true);
    flowNode(c, 380, 70, "My apartment", "#apartment", false, false);
    flowNode(c, 630, 70, "My lease", "#lease", false, false);
    flowNode(c, 880, 70, "My bills · history", "#bills · #payments", false, false);
    flowArrow(c, 300, 90, 378, 90, "");
    flowArrow(c, 540, 90, 628, 90, "");
    flowArrow(c, 790, 90, 878, 90, "");
    flowNode(c, 380, 280, "Maintenance", "/maintenance", false, true);
    flowNode(c, 720, 280, "Submit request", "/maintenance/create", false, true);
    flowNode(c, 40, 280, "Profile", "/profile", false, true);
    flowArrow(c, 380, 400, 380, 344, ""); flowArrow(c, 40, 392, 40, 344, "");
    flowArrow(c, 640, 300, 718, 300, "");
    flowNode(c, 40, 470, "Contact support", "mailto:support@staytrack.ph", false, true);
    flowNode(c, 380, 470, "Sign out", "/logout", false, true);
  });
}

// ---------------------------------------------------------------------
//  Section 02/03 — Assemble admin & tenant frames
// ---------------------------------------------------------------------
function buildS2(page) {
  const frames = [
    ["02 · Dashboard  —  /dashboard", () => sAdminDashboard(pageframe()), 1100],
    ["02 · Dashboard — notifications open", () => {
      sAdminDashboard(pageframe());
      notificationPanel(lastPlace, 806, 84);
    }, 1100],
    ["02 · Apartments  —  /apartments", () => sApartmentsList(pageframe()), 700],
    ["02 · Apartments — delete confirmation modal", () => {
      sApartmentsList(pageframe());
      const back = frame("modal-backdrop", 0, 0, css.pageW, lastPlace.height, hex2rgb("#0f172a"));
      back.fills = [{ type: "SOLID", color: hex2rgb("#0f172a"), opacity: 0.58 }];
      deleteModal(back, (css.pageW - 440) / 2, (lastPlace.height - 260) / 2, "Apartment A-101");
    }, 700],
    ["02 · Apartment create  —  /apartments/create", () => sApartmentForm(pageframe(), "create"), 860],
    ["02 · Apartment A-101  —  /apartments/1", () => sApartmentShow(pageframe()), 680],
    ["02 · Apartment edit  —  /apartments/1/edit", () => sApartmentForm(pageframe(), "edit"), 860],
    ["02 · Tenants  —  /tenants", () => sTenantsList(pageframe(), false), 940],
    ["02 · Tenants — quick create open", () => sTenantsList(pageframe(), true), 1100],
    ["02 · Tenant Jm Carig  —  /tenants/2", () => sTenantShow(pageframe()), 660],
    ["02 · Tenant create  —  /tenants/create", () => sTenantForm(pageframe()), 700],
    ["02 · Leases  —  /leases", () => sLeasesList(pageframe()), 580],
    ["02 · Lease create  —  /leases/create", () => sLeaseForm(pageframe(), "create"), 860],
    ["02 · Lease #3  —  /leases/3", () => sLeaseShow(pageframe()), 680],
    ["02 · Bills  —  /bills", () => sBillsList(pageframe()), 640],
    ["02 · Bill create  —  /bills/create", () => sBillForm(pageframe(), "create"), 860],
    ["02 · Bill #3  —  /bills/3", () => sBillShow(pageframe()), 680],
    ["02 · Payments  —  /payments", () => sPaymentsList(pageframe()), 500],
    ["02 · Payment create  —  /payments/create", () => sPaymentCreate(pageframe()), 760],
    ["02 · Payment #1  —  /payments/1", () => sPaymentShow(pageframe()), 900],
    ["02 · Maintenance  —  /maintenance", () => sMaintenanceList(pageframe(), true), 660],
    ["02 · Maintenance — empty state", () => sMaintenanceList(pageframe(), false), 600],
    ["02 · Maintenance create  —  /maintenance/create", () => sMaintenanceCreate(pageframe()), 860],
    ["02 · Maintenance request #1  —  /maintenance/1", () => sMaintenanceShow(pageframe()), 680],
    ["02 · Profile  —  /profile", () => sProfile(pageframe()), 600],
  ];
  let current = null;
  function pageframe() { return current; }
  frames.forEach(([label, fn, h]) => {
    const c = place(page, css.pageW, h, label);
    current = c;
    lastPlace = c;
    fn();
  });
}
let lastPlace = null;

function sTenantForm(container) {
  const a = appShell(container, { active: "tenants", user: "StayTrack Admin", email: "admin@staytrack.test", roleLabel: "Administrator", initials: "SA" });
  const X = a.x, Y = a.y, W = a.w;
  backLink(container, X, Y, "Back to tenants");
  let y = Y + 40;
  pageHead(container, X, y, W, {
    eyebrow: "Tenants",
    title: "Add tenant",
    sub: "Resident name and primary contact details.",
  });
  y += 96;
  const p = panel(container, X, y, W, 400, css.col.white, 14);
  const sec = frame("form-section", 20, 20, W - 40, 60, null);
  text(sec, 0, 0, 24, "1", 22, 700, css.col.slate200, { lineHeight: 28 });
  text(sec, 32, 0, 400, "Personal information", 16, 700, css.col.ink, { lineHeight: 22 });
  text(sec, 32, 26, 400, "Resident name and primary contact details.", 13, 400, css.col.slate500, { lineHeight: 18 });
  field(container, X + 20, y + 100, (W - 64) / 2, "First name *", "First name", {});
  field(container, X + 20 + (W - 64) / 2 + 24, y + 100, (W - 64) / 2, "Last name *", "Last name", {});
  field(container, X + 20, y + 196, (W - 64) / 2, "Phone", "09XXXXXXXXX", {});
  field(container, X + 20 + (W - 64) / 2 + 24, y + 196, (W - 64) / 2, "Email", "tenant@example.com", {});
  button(container, X + W - 132, y + 300, 112, "Add tenant", "primary", "plus");
}

function buildS3(page) {
  const frames = [
    ["03 · Tenant dashboard  —  /dashboard", () => sTenantDashboard(pageframe(), false), 1060],
    ["03 · Tenant dashboard — no active lease", () => sTenantDashboard(pageframe(), true), 1180],
    ["03 · Maintenance  —  /maintenance", () => sMaintenanceList(pageframe(), true), 660],
    ["03 · Maintenance create  —  /maintenance/create", () => sMaintenanceCreate(pageframe()), 860],
    ["03 · Profile  —  /profile", () => sTenantProfile(pageframe()), 600],
  ];
  let current = null;
  function pageframe() { return current; }
  frames.forEach(([label, fn, h]) => {
    const c = place(page, css.pageW, h, label);
    current = c;
    lastPlace = c;
    fn();
  });
}

// ---------------------------------------------------------------------
//  Main
// ---------------------------------------------------------------------
async function main() {
  await loadFonts();
  const ancient = figma.root.children;
  const p1 = newPage("01 · Landing & Auth");
  const p2 = newPage("02 · Admin");
  const p3 = newPage("03 · Tenant");
  const p4 = newPage("04 · Components");
  const p5 = newPage("05 · Prototype Flows");
  buildS1(p1);
  buildS2(p2);
  buildS3(p3);
  buildS4(p4);
  buildS5(p5);
  figma.currentPage = p1;
  figma.viewport.scrollAndZoomIntoView([p1]);
  figma.closePlugin();
}

main();