const ids=['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867'];
const out=[];
for(const id of ids){const n=figma.currentPage.findAll(x=>x.id===id)[0];out.push(n?{id,name:n.name,type:n.type,width:n.width,height:n.height,reactions:n.reactions?.length??0,children:n.children.length}: {id,missing:true});}
return out;
