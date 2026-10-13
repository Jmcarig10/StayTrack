const dashboard = await figma.getNodeByIdAsync('284:5601');
if (!dashboard) return JSON.stringify({ error: 'Admin HD dashboard not found' });

const targetNames = new Set([
  'NEW FLOW / Admin HD / Maintenance / Index',
  'NEW FLOW / Admin HD / Bills / Index',
  'NEW FLOW / Admin HD / Payments / Index',
  'NEW FLOW / Admin HD / Profile',
  'NEW FLOW / Admin HD / Notifications / Panel'
]);
for (const n of [...figma.currentPage.children]) if (targetNames.has(n.name)) n.remove();

await Promise.all([
  figma.loadFontAsync({ family: 'Figtree', style: 'Regular' }),
  figma.loadFontAsync({ family: 'Figtree', style: 'Bold' }),
  figma.loadFontAsync({ family: 'Inter', style: 'Regular' })
]);

const C = {
  navy: { r: 0.0588, g: 0.0902, b: 0.1647 },
  ink: { r: 0.0588, g: 0.0902, b: 0.1647 },
  text: { r: 0.2000, g: 0.2549, b: 0.3333 },
  muted: { r: 0.3922, g: 0.4549, b: 0.5451 },
  eyebrow: { r: 0.5804, g: 0.6392, b: 0.7216 },
  bg: { r: 0.9725, g: 0.9804, b: 0.9882 },
  white: { r: 1, g: 1, b: 1 },
  border: { r: 0.8588, g: 0.8941, b: 0.9373 },
  pale: { r: 0.9569, g: 0.9686, b: 0.9843 },
  green: { r: 0.0000, g: 0.5608, b: 0.4000 },
  greenPale: { r: 0.9020, g: 0.9804, b: 0.9490 },
  amber: { r: 0.8500, g: 0.4700, b: 0.0000 },
  amberPale: { r: 1.0000, g: 0.9686, b: 0.8824 },
  red: { r: 0.8500, g: 0.1200, b: 0.1200 },
  redPale: { r: 1.0000, g: 0.9255, b: 0.9255 },
  blue: { r: 0.1176, g: 0.3804, b: 0.9608 },
  bluePale: { r: 0.9137, g: 0.9490, b: 1.0000 }
};
const solid = color => [{ type: 'SOLID', color }];
const all = (n, out = []) => { out.push(n); if (n.children) for (const c of n.children) all(c, out); return out; };
const iconSources = {};
for (const n of all(dashboard)) if (/^lucide:/.test(n.name || '') && !iconSources[n.name]) iconSources[n.name] = n;
const legacyIconSources = {};
for (const id of ['179:1511', '151:3230', '151:3338']) {
  const root = await figma.getNodeByIdAsync(id);
  if (root) for (const n of all(root)) if (/^Icon \/ (home|bell|grid|building|users|file|receipt|card|user|logout|wallet|maintenance|check|clock|alert|search)$/.test(n.name || '') && n.type === 'FRAME') legacyIconSources[n.name] = n;
}
const iconAliases = {
  'lucide:house': 'Icon / home',
  'lucide:bell': 'Icon / bell',
  'lucide:layout-dashboard': 'Icon / grid',
  'lucide:building-2': 'Icon / building',
  'lucide:users': 'Icon / users',
  'lucide:file-text': 'Icon / file',
  'lucide:receipt': 'Icon / receipt',
  'lucide:credit-card': 'Icon / card',
  'lucide:user-round': 'Icon / user',
  'lucide:log-out': 'Icon / logout',
  'lucide:wallet-cards': 'Icon / wallet',
  'lucide:wrench': 'Icon / maintenance',
  'lucide:chevron-right': 'lucide:arrow-right',
  'lucide:check': 'Icon / check',
  'lucide:clock': 'Icon / clock',
  'lucide:triangle-alert': 'Icon / alert',
  'lucide:alert-triangle': 'Icon / alert',
  'lucide:search': 'Icon / search'
};
const vectorIconSources = {};
for (const key of ['lucide:arrow-right', 'lucide:chevron-down', 'lucide:x']) {
  const found = figma.currentPage.findAll(n => n.name === key && n.type === 'FRAME' && n.children?.length)[0];
  if (found) vectorIconSources[key] = found;
}
const makeFrame = (parent, name, x, y, w, h, fill = C.white, stroke = null, radius = 14) => {
  const n = figma.createFrame(); n.name = name; n.x = x; n.y = y; n.resize(w, h); n.fills = solid(fill); n.cornerRadius = radius;
  if (stroke) { n.strokes = solid(stroke); n.strokeWeight = 1; }
  parent.appendChild(n); return n;
};
const makeText = (parent, name, value, x, y, w, size = 16, color = C.text, bold = false) => {
  const t = figma.createText(); t.name = name; t.characters = value; t.fontName = { family: 'Figtree', style: bold ? 'Bold' : 'Regular' }; t.fontSize = size; t.fills = solid(color); t.x = x; t.y = y; t.resize(w, Math.max(18, size * 1.35)); t.textAutoResize = 'HEIGHT'; parent.appendChild(t); return t;
};
const cloneIcon = (parent, key, x, y, size = 24) => {
  const src = legacyIconSources[iconAliases[key]] || vectorIconSources[iconAliases[key]] || iconSources[key] || legacyIconSources['Icon / home']; if (!src) return null; const c = src.clone(); c.name = key; c.fills = []; c.x = x; c.y = y; c.resize(size, size); parent.appendChild(c); return c;
};
const tintIcon = (icon, color) => {
  for (const n of all(icon)) {
    if (n.type === 'VECTOR' && 'strokes' in n && n.strokes?.length) n.strokes = solid(color);
    if (n.type === 'VECTOR' && 'fills' in n && n.fills?.length) n.fills = solid(color);
  }
  return icon;
};
const replaceShellIcons = root => {
  for (const n of [...all(root)]) {
    if (n.type !== 'RECTANGLE' || !/^lucide:/.test(n.name || '')) continue;
    const src = legacyIconSources[iconAliases[n.name]] || vectorIconSources[iconAliases[n.name]];
    if (!src || !n.parent) continue;
    const parent = n.parent; const x = n.x; const y = n.y; const w = n.width; const h = n.height; const name = n.name;
    const c = src.clone(); c.name = name; c.fills = []; c.x = x; c.y = y; c.resize(w, h); parent.appendChild(c); n.remove();
  }
};
const button = (parent, name, label, x, y, w, variant = 'primary') => {
  const b = makeFrame(parent, name, x, y, w, 54, variant === 'primary' ? C.navy : C.white, variant === 'primary' ? null : C.border, 12);
  makeText(b, 'Label', label, 18, 16, w - 36, 16, variant === 'primary' ? C.white : C.text, true);
  return b;
};
const input = (parent, name, label, x, y, w, value = '') => {
  if (label) makeText(parent, `${name} label`, label, x, y, w, 16, C.text, true);
  const box = makeFrame(parent, `${name} input`, x, label ? y + 28 : y, w, 56, C.white, C.border, 10);
  if (value) makeText(box, 'Value', value, 18, 17, w - 36, 16, C.text, false);
  return box;
};
const activeSidebar = root => {
  const side = root.children.find(n => n.name === 'Sidebar'); if (!side) return;
  const labels = ['Dashboard', 'Apartments', 'Tenants', 'Leases', 'Maintenance', 'Bills', 'Payments', 'Profile'];
  for (const label of labels) {
    const tx = all(side).find(n => n.type === 'TEXT' && n.characters === label); if (!tx) continue;
    const row = tx.parent; const on = label === root.getPluginData?.('active');
    row.fills = solid(on ? C.navy : C.white);
    tx.fills = solid(on ? C.white : C.text);
    const ico = row.children?.find(n => /^lucide:/.test(n.name || ''));
    if (ico) tintIcon(ico, on ? C.white : C.text);
  }
};
const shell = (name, x, y, active) => {
  const root = dashboard.clone(); root.name = name; root.x = x; root.y = y; root.visible = true; root.setPluginData('active', active);
  replaceShellIcons(root);
  activeSidebar(root);
  const chevron = all(root).find(n => n.name === 'lucide:chevron-right');
  if (chevron) tintIcon(chevron, C.muted);
  const main = root.children.find(n => n.name === 'Main'); const content = main.children.find(n => n.name === 'Content');
  for (const c of [...content.children]) c.remove();
  content.layoutMode = 'NONE';
  content.fills = solid(C.bg);
  return { root, content };
};
const heading = (content, eyebrow, title, desc, action) => {
  makeText(content, 'Eyebrow', eyebrow, 40, 40, 600, 16, C.eyebrow, true);
  makeText(content, 'Page title', title, 40, 67, 900, 38, C.ink, true);
  makeText(content, 'Page description', desc, 40, 120, 900, 18, C.muted, false);
  if (action) button(content, `Action / ${action}`, action, 1320, 72, 200, 'primary');
};
const stat = (content, x, label, value, icon, tint = C.pale, iconColor = C.text, sub = '') => {
  const card = makeFrame(content, `Stat / ${label}`, x, 190, 480, 118, C.white, C.border, 16);
  const ib = makeFrame(card, 'Icon background', 24, 24, 48, 48, tint, null, 14); const ic = cloneIcon(ib, icon, 12, 12, 24); if (ic) tintIcon(ic, iconColor);
  makeText(card, 'Label', label, 88, 27, 300, 15, C.muted, true); makeText(card, 'Value', value, 88, 54, 330, 28, C.ink, true); if (sub) makeText(card, 'Sub', sub, 88, 88, 330, 15, C.muted, false);
};
const table = (content, name, y, h, columns, search, filters, empty, rows = []) => {
  const panel = makeFrame(content, name, 40, y, 1520, h, C.white, C.border, 18);
  const toolbar = makeFrame(panel, 'Toolbar', 0, 0, 1520, 92, C.white, null, 18);
  input(toolbar, 'Search', '', 26, 18, 420, search);
  let fx = 930; for (const f of filters) { const width = Math.max(72, f.length * 9 + 28); const chip = makeFrame(toolbar, `Filter / ${f}`, fx, 22, width, 48, f === 'All' ? C.navy : C.white, f === 'All' ? null : C.border, 24); makeText(chip, 'Label', f, 14, 14, width - 28, 15, f === 'All' ? C.white : C.muted, f === 'All'); fx += width + 10; }
  makeFrame(panel, 'Header background', 0, 92, 1520, 56, C.pale, null, 0);
  const colW = 1480 / columns.length; columns.forEach((c, i) => makeText(panel, `Header / ${c}`, c, 26 + i * colW, 111, colW - 20, 13, C.eyebrow, true));
  if (rows.length) {
    const rowH = 74;
    rows.forEach((row, ri) => {
      const yy = 148 + ri * rowH;
      if (ri > 0) makeFrame(panel, `Row divider ${ri}`, 0, yy, 1520, 1, C.border, null, 0);
      row.forEach((value, ci) => makeText(panel, `Row ${ri + 1} / ${columns[ci]}`, value, 26 + ci * colW, yy + 22, colW - 20, 14, ci === 0 ? C.ink : C.muted, ci === 0));
    });
  } else {
    makeText(panel, 'Empty state', empty, 26, 178, 1460, 16, C.muted, false);
  }
  return panel;
};

const made = [];

// Maintenance index (real site: empty state with three request metrics).
{
  const { root, content } = shell('NEW FLOW / Admin HD / Maintenance / Index', 23380, 11000, 'Maintenance');
  heading(content, 'MAINTENANCE', 'Maintenance requests', 'Track repair and upkeep requests across the building.', 'New request');
  stat(content, 40, 'Total requests', '0', 'lucide:wrench', C.bluePale, C.blue);
  stat(content, 560, 'Open', '0', 'lucide:clock', C.amberPale, C.amber);
  stat(content, 1080, 'Completed', '0', 'lucide:check', C.bluePale, C.blue);
  table(content, 'Maintenance requests table', 340, 280, ['REQUEST', 'TENANT', 'UNIT', 'CATEGORY', 'PRIORITY', 'STATUS', 'SUBMITTED', 'ACTION'], 'Search title, tenant, or unit...', ['All', 'Pending', 'In progress', 'Completed', 'Cancelled'], 'No maintenance requests found.');
  made.push(root.id);
}

// Bills index (real site values from the current local app).
{
  const { root, content } = shell('NEW FLOW / Admin HD / Bills / Index', 25400, 11000, 'Bills');
  heading(content, 'FINANCE', 'Bills', 'Monitor rent charges, balances, and due dates across active leases.', 'Record payment');
  stat(content, 40, 'SEPTEMBER BILLED', '₱0', 'lucide:receipt', C.pale, C.text);
  stat(content, 560, 'COLLECTED', '₱1,000', 'lucide:check', C.greenPale, C.green);
  stat(content, 1080, 'OUTSTANDING', '₱9,700', 'lucide:clock', C.amberPale, C.amber);
  table(content, 'Bills table', 340, 430, ['BILL / TENANT', 'UNIT', 'BILLING MONTH', 'DUE DATE', 'TOTAL', 'BALANCE', 'STATUS', 'ACTION'], 'Search tenant, unit, or bill ID...', ['All', 'Unpaid', 'Partial', 'Paid', 'Overdue'], '', [
    ['Bill #3\nJuanito Mico', 'Unit A-001', 'August 2026', 'Aug 31, 2026', '₱8,500.00', '₱8,500.00', 'Unpaid', 'Record payment'],
    ['Bill #1\nJOHN MICHAEL CARIG', 'Unit A-101', 'August 2026', 'Jul 28, 2026', '₱1,000.00', '₱0', 'Paid', 'View bill'],
    ['Bill #2\nJm Carig', 'Unit A-101', 'July 2026', 'Aug 01, 2026', '₱1,200.00', '₱1,200.00', 'Unpaid', 'Record payment']
  ]);
  made.push(root.id);
}

// Payment history index (real site has one recorded cash payment).
{
  const { root, content } = shell('NEW FLOW / Admin HD / Payments / Index', 27420, 11000, 'Payments');
  heading(content, 'FINANCE', 'Payment history', 'Review and reconcile all recorded tenant payments.', 'Record payment');
  table(content, 'Payment history table', 190, 330, ['TENANT', 'UNIT', 'PAYMENT DATE', 'AMOUNT', 'METHOD', 'REFERENCE', 'RECEIVED BY', 'ACTION'], 'Search tenant, unit, or reference...', ['All methods', 'Export'], '', [
    ['JOHN MICHAEL CARIG\nBill #1', 'Unit A-101', 'Jul 29, 2026', '₱1,000.00', 'Cash', '—', 'StayTrack Admin', 'View details']
  ]);
  made.push(root.id);
}

// Profile settings (real website structure: personal info, password, danger zone).
{
  const { root, content } = shell('NEW FLOW / Admin HD / Profile', 29440, 11000, 'Profile');
  heading(content, 'ACCOUNT', 'Profile settings', 'Manage your personal information, password, and account access.');
  const profile = makeFrame(content, 'Profile card', 40, 190, 310, 650, C.white, C.border, 18);
  const av = makeFrame(profile, 'Profile avatar', 105, 38, 100, 100, C.navy, null, 50); makeText(av, 'Initials', 'AD', 29, 34, 42, 26, C.white, true);
  makeText(profile, 'Name', 'Admin', 80, 154, 150, 20, C.ink, true); makeText(profile, 'Role', 'Administrator', 80, 184, 170, 16, C.muted, false);
  for (const [i, label] of ['Personal information', 'Password & security', 'Danger zone'].entries()) { const row = makeFrame(profile, `Profile tab / ${label}`, 16, 240 + i * 58, 278, 44, i === 0 ? C.navy : C.white, null, 10); const ic = cloneIcon(row, i === 2 ? 'lucide:triangle-alert' : i === 1 ? 'lucide:file-text' : 'lucide:user-round', 16, 10, 22); if (ic) tintIcon(ic, i === 0 ? C.white : i === 2 ? C.red : C.muted); makeText(row, 'Label', label, 54, 11, 210, 15, i === 0 ? C.white : C.muted, true); }
  const personal = makeFrame(content, 'Personal information panel', 390, 190, 1130, 330, C.white, C.border, 18); const personalIcon = cloneIcon(personal, 'lucide:user-round', 40, 40, 28); if (personalIcon) tintIcon(personalIcon, C.navy); makeText(personal, 'Panel title', 'Personal information', 84, 40, 430, 22, C.ink, true); makeText(personal, 'Panel helper', 'Update your name and email address.', 84, 70, 520, 16, C.muted, false);
  input(personal, 'Full name', 'Full name *', 40, 122, 450, 'Admin'); input(personal, 'Email', 'Email address *', 560, 122, 450, 'admin123@gmail.com'); input(personal, 'Role', 'Role', 40, 218, 450, 'Administrator'); button(personal, 'Save changes', 'Save changes', 850, 250, 240, 'primary');
  const pass = makeFrame(content, 'Update password panel', 390, 550, 1130, 260, C.white, C.border, 18); const passIcon = cloneIcon(pass, 'lucide:file-text', 40, 40, 28); if (passIcon) tintIcon(passIcon, C.navy); makeText(pass, 'Panel title', 'Update password', 84, 40, 400, 22, C.ink, true); makeText(pass, 'Panel helper', 'Use at least eight characters with a number or symbol.', 84, 70, 700, 16, C.muted, false); input(pass, 'Current', 'Current password', 40, 112, 320, ''); input(pass, 'New', 'New password', 390, 112, 320, ''); input(pass, 'Confirm', 'Confirm new password', 740, 112, 320, ''); button(pass, 'Update password', 'Update password', 820, 198, 240, 'primary');
  const danger = makeFrame(content, 'Danger zone', 390, 840, 1130, 120, C.redPale, { r: 1, g: 0.70, b: 0.70 }, 18); makeText(danger, 'Title', 'Delete account', 30, 24, 350, 18, C.red, true); makeText(danger, 'Copy', 'Permanently remove this account and all associated access.', 30, 54, 650, 15, C.red, false); const delBtn = button(danger, 'Delete account', 'Delete account', 850, 28, 220, 'secondary'); delBtn.strokes = solid(C.red); const delText = delBtn.children.find(n => n.type === 'TEXT'); if (delText) delText.fills = solid(C.red);
  made.push(root.id);
}

// Notification panel (standalone overlay component based on app.blade.php).
{
  const p = figma.createFrame(); p.name = 'NEW FLOW / Admin HD / Notifications / Panel'; p.x = 31500; p.y = 11000; p.resize(520, 420); p.fills = solid(C.white); p.cornerRadius = 18; p.strokes = solid(C.border); p.strokeWeight = 1; p.visible = true;
  makeText(p, 'Eyebrow', 'UPDATES', 28, 24, 200, 13, C.eyebrow, true); makeText(p, 'Title', 'Notifications', 28, 48, 300, 24, C.ink, true); makeText(p, 'Close', '×', 458, 24, 32, 28, C.muted, false);
  const card = makeFrame(p, 'Unread notification', 0, 98, 520, 126, C.bluePale, null, 0); const bell = cloneIcon(card, 'lucide:bell', 28, 28, 24); if (bell) tintIcon(bell, C.blue); makeText(card, 'Heading', 'Review your latest activity', 72, 24, 390, 16, C.ink, true); makeText(card, 'Copy', 'Open the dashboard to see recent updates.', 72, 53, 390, 15, C.muted, false); makeText(card, 'Time', 'Just now', 72, 84, 200, 13, C.eyebrow, false);
  makeFrame(p, 'Divider', 28, 258, 464, 1, C.border, null, 0); makeText(p, 'Mark read', 'Mark all as read', 28, 292, 464, 16, C.text, true);
}

// Repair the only visible placeholder values in existing Admin HD forms.
for (const id of ['284:5867', '284:6086']) { const n = await figma.getNodeByIdAsync(id); if (n) for (const t of n.findAll(x => x.type === 'TEXT' && x.characters === 'undefined')) { t.characters = ''; } }

return JSON.stringify({ created: made, notificationPanel: 'NEW FLOW / Admin HD / Notifications / Panel' });
