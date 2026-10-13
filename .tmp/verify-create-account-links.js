const ids=['272:407','272:409','275:971','275:973','272:340','272:345','275:904','275:909','309:256','309:258'];
const out=[];
for(const id of ids){
  const n=await figma.getNodeByIdAsync(id);
  out.push({id,name:n?.name,parent:n?.parent?.name,reactions:(n?.reactions||[]).flatMap(r=>(r.actions||[]).map(a=>({type:a.type,to:a.destinationId||null,nav:a.navigation||null})))});
}
const frame=await figma.getNodeByIdAsync('309:198');
const login=await figma.getNodeByIdAsync('275:913');
return {frame:{id:frame?.id,name:frame?.name,parent:frame?.parent?.name,w:frame?.width,h:frame?.height},login:{id:login?.id,name:login?.name,parent:login?.parent?.name},links:out};
