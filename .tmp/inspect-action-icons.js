const root = figma.currentPage.findAll(n => n.id === '284:5720')[0];
const table = root.findAll(n => n.name === 'Apartments table')[0];
return table ? table.findAll(n => true).map(n => ({ id:n.id, name:n.name, type:n.type, x:n.x, y:n.y, w:n.width, h:n.height, childCount:n.children?.length ?? null, childTypes:n.children?.map(c=>c.type) ?? [] })).slice(-80) : [];
