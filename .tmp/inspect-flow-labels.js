const all=(n,o=[])=>{o.push(n);if(n.children)for(const c of n.children)all(c,o);return o};
return all(figma.root).filter(n=>n.type==='TEXT'&&/flow|admin|tenant/i.test(n.characters||'')).slice(-100).map(n=>({id:n.id,text:n.characters,parent:n.parent?.name,parentId:n.parent?.id}));
