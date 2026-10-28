const all=(n,o=[])=>{o.push(n);if(n.children)for(const c of n.children)all(c,o);return o};
return all(figma.root).filter(n=>/admin|flow 3|03/i.test(n.name||'')).map(n=>({id:n.id,type:n.type,name:n.name,parent:n.parent?.name,x:n.x,y:n.y,w:n.width,h:n.height}));
