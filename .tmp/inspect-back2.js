const ids=['277:1415','278:1825','278:2163','278:2501','278:2839','278:3177','278:3681','278:3761','275:860','275:913'];
const all=(x,out=[])=>{out.push(x);if(x.children)for(const c of x.children)all(c,out);return out;};
const results=[];
for(const id of ids){
  const root=await figma.getNodeByIdAsync(id); if(!root){results.push({id,missing:true});continue;}
  const texts=all(root).filter(n=>n.type==='TEXT' && /back|edit|delete|sign out|save|cancel|logout/i.test(n.characters)).map(n=>({id:n.id,text:n.characters,parent:n.parent&&n.parent.id,parentType:n.parent&&n.parent.type,reactions:n.reactions||[]}));
  results.push({id,name:root.name,texts});
}
return results;
