const n=await figma.getNodeByIdAsync('204:318');
const walk=(x,o=[])=>{o.push({id:x.id,name:x.name,type:x.type,x:x.x,y:x.y,w:x.width,h:x.height,visible:x.visible,children:x.children?.length||0});if(x.children)for(const c of x.children)walk(c,o);return o};
return walk(n);
