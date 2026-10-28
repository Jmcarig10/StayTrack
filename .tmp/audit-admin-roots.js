const ids=['18:8','18:167','18:288','18:459','18:574','18:895','18:1007','18:1128','131:943','131:944','131:946','151:2232','151:2347','151:2458','151:2567','151:2675','151:2787','151:2788','151:2896','151:3008','151:3009','151:3117','151:3229','151:3230','151:3338','151:3450','151:3643','151:3679','151:3741','151:3864','179:1511','179:1731'];
const out=[];
for(const id of ids){const root=await figma.getNodeByIdAsync(id);if(!root){out.push({id,missing:true});continue;}const a=root.findAll(n=>(n.reactions||[]).length>0);out.push({id,name:root.name,actions:a.length,samples:a.slice(0,8).map(n=>({node:n.id,name:n.name,text:n.type==='TEXT'?n.characters:null,to:n.reactions.flatMap(r=>r.actions.map(x=>x.destinationId||null))}))});}
return out;
