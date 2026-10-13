const n=await figma.getNodeByIdAsync('284:5601');
const all=(x,o=[])=>{o.push(x);if(x.children)for(const c of x.children)all(c,o);return o};
return all(n).filter(x=>/^lucide:/.test(x.name||'')).map(x=>({id:x.id,name:x.name,type:x.type,children:x.children?.length||0}));
