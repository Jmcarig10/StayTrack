const ids=['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867'];
const out=[];
for(const id of ids){const root=await figma.getNodeByIdAsync(id);if(!root)continue;out.push({id,name:root.name,texts:root.findAll(n=>n.type==='TEXT').map(n=>({id:n.id,text:n.characters,parent:n.parent?.name,pp:n.parent?.parent?.name,x:n.x,y:n.y,w:n.width,h:n.height}))});}
return out;
