const ids=['219:2824','219:2975','219:2814','219:2965','166:842','170:1133','178:1454','178:1455','170:1291','170:1294'];
const out=[];
for(const id of ids){
  const n=await figma.getNodeByIdAsync(id);
  const kids=n&&n.children?n.children.map(c=>({id:c.id,name:c.name,type:c.type,reactions:(c.reactions||[]).map(r=>({trigger:r.trigger?.type,actions:(r.actions||[]).map(a=>({type:a.type,destinationId:a.destinationId,navigation:a.navigation}))}))})):null;
  out.push({id,name:n?.name,type:n?.type,reactions:(n?.reactions||[]).map(r=>({trigger:r.trigger?.type,actions:(r.actions||[]).map(a=>({type:a.type,destinationId:a.destinationId,navigation:a.navigation}))})),children:kids});
}
return out;
