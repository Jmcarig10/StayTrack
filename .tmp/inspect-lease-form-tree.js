const ids = ['284:6751','284:6867'];
const walk = n => ({id:n.id,name:n.name,type:n.type,x:n.x,y:n.y,w:n.width,h:n.height,chars:n.type==='TEXT'?n.characters:undefined,children:n.children?.map(walk)});
return ids.map(id => { const root=figma.currentPage.findAll(n=>n.id===id)[0]; const c=root?.children.find(n=>n.name==='Main')?.children.find(n=>n.name==='Content'); return {id, panel:c?.children.find(n=>n.y===171) ? walk(c.children.find(n=>n.y===171)) : null}; });
