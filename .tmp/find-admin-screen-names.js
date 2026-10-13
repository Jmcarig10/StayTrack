const all=(n,o=[])=>{o.push(n);if(n.children)for(const c of n.children)all(c,o);return o};
return all(figma.root).filter(n=>n.type==='FRAME'&&/record payment|payment details|create lease|add apartment|apartment a-301|maintenance requests|bills|payment history|profile/i.test(n.name||'')).map(n=>({id:n.id,name:n.name,parent:n.parent?.name,parentId:n.parent?.id,x:n.x,y:n.y,w:n.width,h:n.height}));
