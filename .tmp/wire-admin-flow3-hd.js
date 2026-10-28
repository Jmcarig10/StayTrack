try {
const hd = {
  dashboard: '284:5601',
  apartmentsIndex: '284:5720',
  apartmentsCreate: '284:5867',
  apartmentsShow: '284:5983',
  apartmentsEdit: '284:6086',
  tenantsIndex: '284:6189',
  tenantsCreate: '284:6327',
  tenantsShow: '284:6430',
  tenantsEdit: '284:6533',
  leasesIndex: '284:6636',
  leasesCreate: '284:6751',
  leasesShow: '284:6867',
  maintenance: '179:1511',
  bills: '151:3230',
  payments: '151:3338',
  paymentCreate: '151:3864',
  profile: '131:946',
  notifications: '179:1731',
  logout: '275:913'
};

const get = id => figma.getNodeByIdAsync(id);
const all = (n, out = []) => { out.push(n); if (n.children) for (const c of n.children) all(c, out); return out; };
const textNodes = (root, value) => all(root).filter(n => n.type === 'TEXT' && n.characters === value);
const topFrame = n => { let x = n; while (x && x.parent && x.parent.type !== 'PAGE') x = x.parent; return x; };
const hotspotCache = new Map();
const setNav = async (from, to) => {
  if (!from || !to || typeof from.setReactionsAsync !== 'function') return false;
  const root = topFrame(from);
  if (root?.id === to.id) return false;
  let source = from;
  // Admin HD screens were generated with deeply nested controls. Figma only
  // accepts prototype sources for these file nodes when the source is a direct
  // child of the top-level frame, so add an invisible hotspot over the control.
  if (root && from.parent && from.parent.id !== root.id) {
    const key = `${root.id}|${from.id}`;
    source = hotspotCache.get(key);
    if (!source) {
      const rb = root.absoluteRenderBounds, sb = from.absoluteRenderBounds;
      if (!rb || !sb) return false;
      source = figma.createFrame();
      source.name = `Prototype hotspot / ${from.name || from.id}`;
      source.x = sb.x - rb.x; source.y = sb.y - rb.y;
      source.resize(Math.max(1, sb.width), Math.max(1, sb.height));
      source.opacity = 0; source.fills = []; source.strokes = [];
      root.appendChild(source);
      hotspotCache.set(key, source);
    }
  }
  const action = { type: 'NODE', destinationId: to.id, navigation: 'NAVIGATE', transition: { type: 'DISSOLVE', duration: 0.2, easing: { type: 'EASE_IN_AND_OUT' } }, resetVideoPosition: false };
  await source.setReactionsAsync([{ action, actions: [action], trigger: { type: 'ON_CLICK' } }]);
  return true;
};
const wireText = async (root, value, target, mode = 'first') => {
  const nodes = textNodes(root, value);
  const chosen = mode === 'all' ? nodes : nodes.slice(0, 1);
  let count = 0;
  for (const tx of chosen) if (await setNav(tx.parent, target)) count++;
  return count;
};
const wireRegex = async (root, re, target, mode = 'first') => {
  const nodes = all(root).filter(n => n.type === 'TEXT' && re.test(n.characters));
  const chosen = mode === 'all' ? nodes : nodes.slice(0, 1);
  let count = 0;
  for (const tx of chosen) if (await setNav(tx.parent, target)) count++;
  return count;
};
const summary = { sidebar: 0, cta: 0, crud: 0, bells: 0, patchedLegacy: 0, missing: [], details: [] };
const targets = {};
for (const [key, id] of Object.entries(hd)) { targets[key] = await get(id); if (!targets[key]) summary.missing.push({ key, id }); }

const sidebarMap = {
  Dashboard: 'dashboard', Apartments: 'apartmentsIndex', Tenants: 'tenantsIndex', Leases: 'leasesIndex',
  Maintenance: 'maintenance', Bills: 'bills', Payments: 'payments', Profile: 'profile'
};
const hdRoots = [
  ['dashboard', 'dashboard'], ['apartmentsIndex', 'apartmentsIndex'], ['apartmentsCreate', 'apartmentsCreate'],
  ['apartmentsShow', 'apartmentsShow'], ['apartmentsEdit', 'apartmentsEdit'], ['tenantsIndex', 'tenantsIndex'],
  ['tenantsCreate', 'tenantsCreate'], ['tenantsShow', 'tenantsShow'], ['tenantsEdit', 'tenantsEdit'],
  ['leasesIndex', 'leasesIndex'], ['leasesCreate', 'leasesCreate'], ['leasesShow', 'leasesShow']
];

for (const [rootKey, screenKey] of hdRoots) {
  const root = targets[rootKey];
  if (!root) continue;
  const sidebars = all(root).filter(n => n.type === 'FRAME' && /sidebar/i.test(n.name || ''));
  const sidebar = sidebars[0];
  if (sidebar) {
    for (const [label, targetKey] of Object.entries(sidebarMap)) {
      const tx = textNodes(sidebar, label)[0];
      if (tx && await setNav(tx.parent, targets[targetKey])) summary.sidebar++;
    }
    const sign = textNodes(sidebar, 'Sign out')[0];
    if (sign && await setNav(sign.parent, targets.logout)) summary.sidebar++;
  }
  const bell = all(root).find(n => /bell/i.test(n.name || ''));
  if (bell && await setNav(bell.parent || bell, targets.notifications)) summary.bells++;
  summary.details.push({ root: root.id, name: root.name, sidebar: !!sidebar, bell: !!bell });
}

summary.cta += await wireText(targets.dashboard, 'Create lease', targets.leasesCreate);
summary.cta += await wireText(targets.dashboard, 'Record payment', targets.paymentCreate);
summary.cta += await wireText(targets.apartmentsIndex, '＋ Add apartment', targets.apartmentsCreate);
summary.cta += await wireRegex(targets.tenantsIndex, /Add tenant/, targets.tenantsCreate);
summary.cta += await wireRegex(targets.leasesIndex, /Create lease/, targets.leasesCreate);

summary.crud += await wireText(targets.apartmentsCreate, 'Cancel', targets.apartmentsIndex);
summary.crud += await wireText(targets.apartmentsCreate, 'Save apartment', targets.apartmentsShow);
summary.crud += await wireText(targets.apartmentsShow, 'Edit apartment', targets.apartmentsEdit);
summary.crud += await wireText(targets.apartmentsShow, 'Back to apartments', targets.apartmentsIndex);
summary.crud += await wireText(targets.apartmentsEdit, 'Cancel', targets.apartmentsShow);
summary.crud += await wireText(targets.apartmentsEdit, 'Save changes', targets.apartmentsShow);

summary.crud += await wireText(targets.tenantsCreate, 'Cancel', targets.tenantsIndex);
summary.crud += await wireText(targets.tenantsCreate, 'Save changes', targets.tenantsShow);
summary.crud += await wireText(targets.tenantsShow, 'Edit tenant', targets.tenantsEdit);
summary.crud += await wireText(targets.tenantsShow, 'Back to tenants', targets.tenantsIndex);
summary.crud += await wireText(targets.tenantsEdit, 'Cancel', targets.tenantsShow);
summary.crud += await wireText(targets.tenantsEdit, 'Save changes', targets.tenantsShow);

summary.crud += await wireText(targets.leasesCreate, 'Cancel', targets.leasesIndex);
summary.crud += await wireText(targets.leasesCreate, 'Create active lease', targets.leasesShow);
summary.crud += await wireText(targets.leasesShow, 'Back to leases', targets.leasesIndex);
summary.crud += await wireText(targets.leasesShow, 'Edit lease', targets.leasesShow);

// Existing Admin presentation screens had a payment CTA pointing into a Tenant section.
// Keep the visual screens unchanged, but correct only that destination for role separation.
for (const id of ['151:2232', '151:2347', '151:2458', '151:3230', '151:3338', '151:3450', '151:3679', '151:3741', '151:3864', '179:1511']) {
  const root = await get(id); if (!root) continue;
  for (const n of all(root)) {
    if (n.name === 'Go / payment-create' && await setNav(n, targets.paymentCreate)) summary.patchedLegacy++;
  }
}

// Close the Admin notification panel back to the Admin dashboard.
if (targets.notifications) {
  const close = all(targets.notifications).find(n => /close|dismiss|x/i.test(n.name || '') && n.type !== 'TEXT');
  if (close && await setNav(close, targets.dashboard)) summary.cta++;
}

return JSON.stringify(summary);
} catch (e) {
  return JSON.stringify({ error: String(e), stack: e && e.stack });
}
