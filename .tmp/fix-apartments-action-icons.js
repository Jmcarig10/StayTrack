const root = figma.currentPage.findAll(n => n.id === '284:5720')[0];
if (!root) return { error: 'Apartments frame not found' };
const main = root.children.find(n => n.name === 'Main');
const content = main?.children.find(n => n.name === 'Content');
const panel = content?.children.find(n => n.name === 'Apartments table');
const addButton = content?.children.find(n => n.name === 'Action / Add apartment');
if (!content || !panel || !addButton) return { error: 'Expected Apartments nodes missing' };
const svg = {
  plus: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round"/></svg>',
  eye: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="#64748B" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.5" stroke="#64748B" stroke-width="1.8"/></svg>',
  edit: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  trash: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 6h18M8 6V3h8v3M19 6l-1 15H6L5 6M10 10v7M14 10v7" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>'
};
const created = [];
const addSvg = (parent, name, markup, x, y, size) => {
  if (parent.children.some(n => n.name === name)) return null;
  const n = figma.createNodeFromSvg(markup);
  n.name = name;
  n.resize(size, size);
  n.x = x;
  n.y = y;
  parent.appendChild(n);
  created.push(n.id);
  return n;
};
addSvg(addButton, 'Icon / plus', svg.plus, 18, 16, 24);
const rows = ['B-101','A-002','A-001','A-101'];
rows.forEach((unit, i) => {
  const y = 156 + i * 91 + 32;
  addSvg(panel, 'Action / view ' + unit, svg.eye, 1200, y, 22);
  addSvg(panel, 'Action / edit ' + unit, svg.edit, 1260, y, 22);
  addSvg(panel, 'Action / delete ' + unit, svg.trash, 1320, y, 22);
});
return { updated: root.id, createdNodeIds: created, prototypeConnectionsChanged: false, searchText: 'Search unit...' };
