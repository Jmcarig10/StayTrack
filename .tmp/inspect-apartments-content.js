const root = figma.currentPage.findAll(n => n.id === '284:5720')[0];
if (!root) return { error: 'missing root' };
const main = root.children.find(n => n.name === 'Main');
const content = main?.children.find(n => n.name === 'Content');
if (!content) return { error: 'missing content' };
return { content: { id: content.id, width: content.width, height: content.height, visible: content.visible, childCount: content.children.length }, children: content.children.map(n => ({ id: n.id, name: n.name, type: n.type, visible: n.visible, x: n.x, y: n.y, width: n.width, height: n.height })) };
