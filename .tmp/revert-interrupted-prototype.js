const ids=['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867'];
const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);if(!n)continue;for(const x of n.findAll(y=>(y.reactions||[]).length>0))await x.setReactionsAsync([]);out.push({id,cleared:true});}
const test=await figma.getNodeByIdAsync('322:541');if(test){test.remove();out.push({id:'322:541',removed:true});}
const legacy=await figma.getNodeByIdAsync('151:2255');if(legacy){const action={type:'NODE',destinationId:'151:2347',navigation:'NAVIGATE',transition:{type:'DISSOLVE',easing:{type:'EASE_OUT'},duration:.2},resetVideoPosition:false};await legacy.setReactionsAsync([{action,actions:[action],trigger:{type:'ON_CLICK'}}]);out.push({id:'151:2255',restored:'151:2347'});}
return JSON.stringify(out);
