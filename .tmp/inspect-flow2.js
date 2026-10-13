const ids = ['278:3503', '278:3944', '283:1899'];
const out = [];
for (const id of ids) {
  const n = await figma.getNodeByIdAsync(id);
  out.push(n ? { id: n.id, name: n.name, type: n.type, width: n.width, height: n.height, children: n.children ? n.children.length : 0 } : null);
}
return out;
