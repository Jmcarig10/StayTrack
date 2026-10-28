const root = figma.currentPage.findAll(n => n.id === '284:5720')[0];
const texts = root.findAll(n => n.type === 'TEXT').map(n => ({ name:n.name, characters:n.characters, width:n.width, height:n.height }));
const search = root.findAll(n => n.name === 'Search input')[0];
return { emailTextNodes: texts.filter(t => t.characters.includes('admin123@gmail.com')), search: search ? { id:search.id, width:search.width, height:search.height, childNames:search.children.map(n=>n.name) } : null, collapsedText: texts.filter(t => t.width < 40 && t.characters.length > 4) };
