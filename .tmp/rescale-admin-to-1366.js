const ids=['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867','358:5091','358:5295','358:5524','358:5710'];
const scale=1366/1920;const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);if(!n)continue;const before={width:n.width,height:n.height};if(Math.abs(n.width-1366)>0.5||Math.abs(n.height-768)>0.5)n.rescale(scale);out.push({id,name:n.name,before,after:{width:n.width,height:n.height}});}
return {scale,frames:out,notifications:{id:'358:5922',width:(await figma.getNodeByIdAsync('358:5922'))?.width,height:(await figma.getNodeByIdAsync('358:5922'))?.height}};
