const root = figma.currentPage.findAll(n => n.id === '284:6327')[0];
const content = root.children.find(n=>n.name==='Main')?.children.find(n=>n.name==='Content');
const panel = content?.children.find(n=>n.y===171);
const walk = n => ({id:n.id,name:n.name,type:n.type,x:n.x,y:n.y,w:n.width,h:n.height,children:n.children?.map(walk)});
return walk(panel);
