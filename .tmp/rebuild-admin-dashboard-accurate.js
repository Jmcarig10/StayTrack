const root = await figma.getNodeByIdAsync('284:5601');
if (!root) return { error: 'Dashboard frame not found' };
await Promise.all([
  figma.loadFontAsync({ family: 'Figtree', style: 'Regular' }),
  figma.loadFontAsync({ family: 'Figtree', style: 'Bold' })
]);
const C = {
  navy: { r: 0.0588, g: 0.0902, b: 0.1647 }, ink: { r: 0.0588, g: 0.0902, b: 0.1647 },
  text: { r: 0.2000, g: 0.2549, b: 0.3333 }, muted: { r: 0.3922, g: 0.4549, b: 0.5451 },
  eyebrow: { r: 0.3922, g: 0.4549, b: 0.5451 }, bg: { r: 0.9725, g: 0.9804, b: 0.9882 }, white: { r: 1, g: 1, b: 1 },
  border: { r: 0.8588, g: 0.8941, b: 0.9373 }, pale: { r: 0.9569, g: 0.9686, b: 0.9843 },
  green: { r: 0.0000, g: 0.5608, b: 0.4000 }, greenPale: { r: 0.9020, g: 0.9804, b: 0.9490 },
  amber: { r: 0.8500, g: 0.4700, b: 0.0000 }, amberPale: { r: 1.0000, g: 0.9686, b: 0.8824 },
  red: { r: 0.8500, g: 0.1200, b: 0.1200 }, redPale: { r: 1.0000, g: 0.9255, b: 0.9255 },
  blue: { r: 0.1176, g: 0.3804, b: 0.9608 }, bluePale: { r: 0.9137, g: 0.9490, b: 1.0000 }
};
const solid = color => [{ type: 'SOLID', color }];
const all = (n, out = []) => { out.push(n); if (n.children) for (const c of n.children) all(c, out); return out; };
const legacy = {};
for (const id of ['179:1511', '151:3230', '151:3338']) {
  const r = await figma.getNodeByIdAsync(id); if (r) for (const n of all(r)) if (/^Icon \/ (home|bell|grid|building|users|file|receipt|card|user|logout|wallet|maintenance|check|clock|alert|search)$/.test(n.name || '') && n.type === 'FRAME') legacy[n.name] = n;
}
const alias = {
  'lucide:house': 'Icon / home', 'lucide:bell': 'Icon / bell', 'lucide:layout-dashboard': 'Icon / grid',
  'lucide:building-2': 'Icon / building', 'lucide:users': 'Icon / users', 'lucide:file-text': 'Icon / file',
  'lucide:receipt': 'Icon / receipt', 'lucide:credit-card': 'Icon / card', 'lucide:user-round': 'Icon / user',
  'lucide:wallet-cards': 'Icon / wallet', 'lucide:wrench': 'Icon / maintenance', 'lucide:check': 'Icon / check', 'lucide:alert-triangle': 'Icon / alert'
};
const tint = (icon, color) => { for (const n of all(icon)) if (n.type === 'VECTOR' && n.strokes?.length) n.strokes = solid(color); };
const icon = (parent, key, x, y, size, color) => { const src = legacy[alias[key]]; if (!src) return null; const c = src.clone(); c.name = key; c.fills = []; c.x = x; c.y = y; c.resize(size, size); parent.appendChild(c); if (color) tint(c, color); return c; };
const frame = (parent, name, x, y, w, h, fill = C.white, stroke = null, radius = 14) => { const n = figma.createFrame(); n.name = name; n.x = x; n.y = y; n.resize(w, h); n.fills = solid(fill); n.cornerRadius = radius; if (stroke) { n.strokes = solid(stroke); n.strokeWeight = 1; } parent.appendChild(n); return n; };
const text = (parent, name, value, x, y, w, size = 16, color = C.text, bold = false) => { const t = figma.createText(); t.name = name; t.characters = value; t.fontName = { family: 'Figtree', style: bold ? 'Bold' : 'Regular' }; t.fontSize = size; t.fills = solid(color); t.x = x; t.y = y; t.resize(w, Math.max(18, size * 1.35)); t.textAutoResize = 'HEIGHT'; parent.appendChild(t); return t; };
const button = (parent, name, label, x, y, w, variant = 'primary', iconKey = null) => { const b = frame(parent, name, x, y, w, 56, variant === 'primary' ? C.navy : C.white, variant === 'primary' ? null : C.border, 12); let tx = 18; if (iconKey) { icon(b, iconKey, 18, 17, 22, variant === 'primary' ? C.white : C.text); tx = 50; } text(b, 'Label', label, tx, 17, w - tx - 16, 16, variant === 'primary' ? C.white : C.text, true); return b; };
const stat = (parent, name, x, label, value, sub, key, tintFill, keyColor) => {
  const c = frame(parent, name, x, 196, 358, 224, C.white, C.border, 16); c.clipsContent = true;
  const arc = figma.createEllipse(); arc.name = 'Decorative corner'; arc.x = 255; arc.y = -70; arc.resize(180, 180); arc.fills = solid(C.pale); c.appendChild(arc);
  text(c, 'Label', label, 30, 34, 220, 16, C.muted, false); const ib = frame(c, 'Icon background', 266, 30, 60, 60, tintFill, null, 16); icon(ib, key, 18, 18, 24, keyColor);
  text(c, 'Value', value, 30, 108, 240, 38, C.ink, true); text(c, 'Sub', sub, 30, 174, 200, 15, sub.includes('occupied') || sub.includes('total') || sub.includes('collected') ? C.green : C.muted, false);
};
const line = (p, name, x, y, w) => frame(p, name, x, y, w, 1, C.border, null, 0);

const main = root.children.find(n => n.name === 'Main');
const content = main.children.find(n => n.name === 'Content');
for (const c of [...content.children]) c.remove();
content.layoutMode = 'NONE'; content.fills = solid(C.bg); content.clipsContent = true;
text(content, 'Eyebrow', 'ADMIN PORTAL', 40, 48, 400, 16, C.eyebrow, true);
text(content, 'Page title', 'Welcome back, Admin', 40, 82, 820, 38, C.ink, true);
text(content, 'Page description', 'Here is what is happening across your properties today.', 40, 136, 850, 18, C.muted, false);
button(content, 'Record payment', 'Record payment', 1140, 105, 206, 'secondary', 'lucide:credit-card');
button(content, 'Create lease', 'Create lease', 1368, 105, 174, 'primary', null);

stat(content, 'Stat / Total apartments', 40, 'Total apartments', '4', '2 occupied', 'lucide:building-2', C.pale, C.text);
stat(content, 'Stat / Occupied units', 430, 'Occupied units', '2', '50% occupancy', 'lucide:house', C.greenPale, C.green);
stat(content, 'Stat / Active tenants', 820, 'Active tenants', '9', '9 total', 'lucide:users', C.bluePale, C.blue);
stat(content, 'Stat / September revenue', 1210, 'September revenue', '₱0', 'collected this month', 'lucide:wallet-cards', C.amberPale, C.amber);

const recent = frame(content, 'Recent payments', 40, 450, 980, 447, C.white, C.border, 18);
text(recent, 'Title', 'Recent payments', 30, 30, 350, 22, C.ink, true); text(recent, 'Helper', 'Latest tenant transactions', 30, 62, 350, 15, C.muted, false); text(recent, 'View all', 'View all  →', 820, 36, 130, 15, C.text, true); line(recent, 'Header divider', 0, 106, 980);
const headers = [['TENANT', 26], ['UNIT', 384], ['DATE', 540], ['METHOD', 710], ['AMOUNT', 850]]; for (const [h, x] of headers) text(recent, `Header / ${h}`, h, x, 130, 110, 13, C.eyebrow, true);
frame(recent, 'Table header', 0, 108, 980, 54, C.pale, null, 0); for (const [h, x] of headers) text(recent, `Header / ${h}`, h, x, 127, 110, 13, C.eyebrow, true);
const avatar = frame(recent, 'Tenant avatar', 26, 182, 52, 52, C.pale, null, 26); text(avatar, 'Initials', 'JC', 14, 15, 30, 15, C.text, true);
text(recent, 'Tenant', 'JOHN MICHAEL CARIG', 92, 184, 250, 16, C.ink, true); text(recent, 'Bill', 'Bill #1', 92, 210, 180, 14, C.muted, false); text(recent, 'Unit', 'Unit A-101', 384, 198, 120, 15, C.muted, false); text(recent, 'Date', 'Jul 29, 2026', 540, 198, 140, 15, C.muted, false);
const cash = frame(recent, 'Cash badge', 710, 190, 60, 32, C.greenPale, { r: 0.75, g: 0.95, b: 0.85 }, 16); text(cash, 'Label', 'Cash', 13, 8, 40, 13, C.green, true); text(recent, 'Amount', '₱1,000.00', 850, 190, 110, 16, C.ink, true); text(recent, 'Paid', 'Paid', 903, 216, 55, 14, C.green, false);

const billing = frame(content, 'Billing overview', 1050, 450, 492, 447, C.white, C.border, 18);
text(billing, 'Title', 'Billing overview', 30, 30, 300, 22, C.ink, true); text(billing, 'Helper', 'Current financial status', 30, 62, 300, 15, C.muted, false); line(billing, 'Divider', 0, 106, 492);
const billRows = [['Active leases', '3', 'lucide:file-text', C.bluePale, C.blue], ['Unpaid / partial', '2', 'lucide:receipt', C.amberPale, C.amber], ['Overdue bills', '0', 'lucide:alert-triangle', C.redPale, C.red]];
for (const [i, row] of billRows.entries()) { const yy = 132 + i * 76; const ib = frame(billing, `Billing icon ${i}`, 26, yy, 46, 46, row[3], null, 14); icon(ib, row[2], 11, 11, 24, row[4]); text(billing, `Billing label ${i}`, row[0], 88, yy + 13, 260, 16, C.text, false); text(billing, `Billing value ${i}`, row[1], 430, yy + 13, 35, 18, i === 1 ? C.amber : i === 2 ? C.red : C.ink, true); if (i < 2) line(billing, `Billing divider ${i}`, 26, yy + 62, 440); }
button(billing, 'Manage bills', 'Manage bills', 26, 366, 440, 'primary', 'lucide:receipt');

const overview = frame(content, 'Apartment overview', 40, 927, 440, 340, C.white, C.border, 18); text(overview, 'Title', 'Apartment overview', 30, 30, 300, 22, C.ink, true); text(overview, 'Helper', 'Live unit distribution', 30, 62, 300, 15, C.muted, false); line(overview, 'Divider', 0, 106, 440);
const ring = figma.createEllipse(); ring.name = 'Occupancy ring'; ring.x = 34; ring.y = 138; ring.resize(170, 170); ring.fills = []; ring.strokes = solid(C.red); ring.strokeWeight = 26; overview.appendChild(ring); const inner = figma.createEllipse(); inner.name = 'Occupancy ring center'; inner.x = 68; inner.y = 172; inner.resize(102, 102); inner.fills = solid(C.white); overview.appendChild(inner); text(overview, 'Occupancy percent', '50%', 81, 184, 80, 28, C.ink, true); text(overview, 'Occupancy label', 'OCCUPIED', 81, 220, 90, 12, C.muted, true);
const legends = [['Occupied', '2', C.navy], ['Available', '2', C.green], ['Reserved', '0', C.amber], ['Maintenance', '0', C.red]]; for (const [i, l] of legends.entries()) { const yy = 146 + i * 38; const dot = figma.createEllipse(); dot.name = `Legend dot ${i}`; dot.x = 250; dot.y = yy + 5; dot.resize(12, 12); dot.fills = solid(l[2]); overview.appendChild(dot); text(overview, `Legend ${i}`, l[0], 272, yy, 110, 15, C.muted, false); text(overview, `Legend value ${i}`, l[1], 400, yy, 24, 15, C.ink, true); }

const leases = frame(content, 'Recent leases', 510, 927, 1032, 340, C.white, C.border, 18); text(leases, 'Title', 'Recent leases', 30, 30, 300, 22, C.ink, true); text(leases, 'Helper', 'Latest rental agreements', 30, 62, 300, 15, C.muted, false); text(leases, 'View all', 'View all  →', 900, 36, 110, 15, C.text, true); line(leases, 'Divider', 0, 106, 1032);
const leaseRows = [['Juanito Mico', 'Unit A-001 · Aug 01, 2026 – Aug 01, 2027'], ['Jm Carig', 'Unit A-101 · Jul 29, 2026 – Aug 29, 2026'], ['JOHN MICHAEL CARIG', 'Unit A-101 · Jul 29, 2026 – Aug 29, 2026']];
for (const [i, r] of leaseRows.entries()) { const yy = 130 + i * 70; const ib = frame(leases, `Lease icon ${i}`, 22, yy, 46, 46, C.greenPale, null, 14); icon(ib, 'lucide:file-text', 11, 11, 24, C.green); text(leases, `Lease tenant ${i}`, r[0], 82, yy + 8, 220, 16, C.ink, true); text(leases, `Lease details ${i}`, r[1], 280, yy + 8, 470, 15, C.muted, false); const badge = frame(leases, `Lease badge ${i}`, 800, yy + 5, 68, 34, C.greenPale, null, 17); text(badge, 'Label', 'Active', 13, 9, 48, 13, C.green, true); }

return { updated: root.id, sections: ['Metrics', 'Recent payments', 'Billing overview', 'Apartment overview', 'Recent leases'], prototypeConnectionsChanged: false };
