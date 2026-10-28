const ids=['277:1415','278:1825','278:2163','278:2501','278:2839','278:3177','278:3681','278:3761','278:3890','278:3988','278:4013','278:4048','278:4070'];
const all=(x,out=[])=>{out.push(x);if(x.children)for(const c of x.children)all(c,out);return out;};
const out=[];
for(const id of ids){const root=await figma.getNodeByIdAsync(id);if(!root){out.push({id,missing:true});continue;}const ns=all(root).filter(n=>n.reactions&&n.reactions.length).map(n=>({id:n.id,name:n.name,type:n.type,text:n.type==='TEXT'?n.characters:undefined,reactions:n.reactions.map(r=>r.actions?.map(a=>a.destinationId))}));out.push({id,name:root.name,actionNodes:ns});}
return out;
