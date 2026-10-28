const ids=['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867'];
const out=[];
for(const id of ids){
 const root=await figma.getNodeByIdAsync(id); if(!root){out.push({id,missing:true});continue;}
 const actions=root.findAll(n=>(n.reactions||[]).length>0).map(n=>({id:n.id,name:n.name,type:n.type,text:n.type==='TEXT'?n.characters:null,reactions:n.reactions.flatMap(r=>r.actions.map(a=>({type:a.type,to:a.destinationId||null,nav:a.navigation||null}))) }));
 out.push({id,name:root.name,actions});
}
return out;
