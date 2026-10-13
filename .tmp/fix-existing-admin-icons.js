const C = {
  navy: { r: 0.0588, g: 0.0902, b: 0.1647 },
  text: { r: 0.2000, g: 0.2549, b: 0.3333 },
  muted: { r: 0.3922, g: 0.4549, b: 0.5451 },
  white: { r: 1, g: 1, b: 1 }
};
const solid = color => [{ type: 'SOLID', color }];
const all = (n, out = []) => { out.push(n); if (n.children) for (const c of n.children) all(c, out); return out; };
const legacyIconSources = {};
for (const id of ['179:1511', '151:3230', '151:3338']) {
  const root = await figma.getNodeByIdAsync(id);
  if (root) for (const n of all(root)) if (/^Icon \/ (home|bell|grid|building|users|file|receipt|card|user|logout|wallet|maintenance|check|clock|alert|search)$/.test(n.name || '') && n.type === 'FRAME') legacyIconSources[n.name] = n;
}
const aliases = {
  'lucide:house': 'Icon / home', 'lucide:bell': 'Icon / bell', 'lucide:layout-dashboard': 'Icon / grid',
  'lucide:building-2': 'Icon / building', 'lucide:users': 'Icon / users', 'lucide:file-text': 'Icon / file',
  'lucide:receipt': 'Icon / receipt', 'lucide:credit-card': 'Icon / card', 'lucide:user-round': 'Icon / user',
  'lucide:log-out': 'Icon / logout', 'lucide:wallet-cards': 'Icon / wallet', 'lucide:wrench': 'Icon / maintenance',
  'lucide:check': 'Icon / check', 'lucide:clock': 'Icon / clock', 'lucide:triangle-alert': 'Icon / alert',
  'lucide:alert-triangle': 'Icon / alert', 'lucide:search': 'Icon / search'
};
const vectorSources = {};
for (const key of ['lucide:arrow-right', 'lucide:chevron-down', 'lucide:x']) {
  const found = figma.currentPage.findAll(n => n.name === key && n.type === 'FRAME' && n.children?.length)[0];
  if (found) vectorSources[key] = found;
}
aliases['lucide:chevron-right'] = 'lucide:arrow-right';
aliases['lucide:chevron-down'] = 'lucide:chevron-down';
const tint = (icon, color) => { for (const n of all(icon)) if (n.type === 'VECTOR' && 'strokes' in n && n.strokes?.length) n.strokes = solid(color); };
const colorOf = n => n.fills?.[0]?.color || C.text;
const targetIds = ['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867'];
const changed = [];
for (const id of targetIds) {
  const root = await figma.getNodeByIdAsync(id); if (!root) continue;
  for (const n of [...all(root)]) {
    if (n.type !== 'RECTANGLE' || !/^lucide:/.test(n.name || '')) continue;
    const src = legacyIconSources[aliases[n.name]] || vectorSources[aliases[n.name]];
    if (!src || !n.parent) continue;
    const parent = n.parent; const x=n.x, y=n.y, w=n.width, h=n.height, name=n.name, color=colorOf(n);
    const c = src.clone(); c.name=name; c.fills=[]; c.x=x; c.y=y; c.resize(w,h); parent.appendChild(c); tint(c,color); n.remove(); changed.push({frame:root.name,icon:name});
  }
}
return {changed: changed.length, frames: [...new Set(changed.map(x=>x.frame))]};
