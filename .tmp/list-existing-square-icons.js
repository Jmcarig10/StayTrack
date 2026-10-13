const ids=['284:5867','284:5983','284:6086','284:6327','284:6430','284:6533','284:6751','284:6867'];
const out=[];for(const id of ids){const n=await figma.getNodeByIdAsync(id);out.push({name:n.name,icons:n.findAll(x=>x.type==='RECTANGLE'&&/^lucide:/.test(x.name||'')).map(x=>({id:x.id,name:x.name,w:x.width,h:x.height}))});}return out;
