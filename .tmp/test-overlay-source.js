const ids=['219:2749','219:2900'];const to=await figma.getNodeByIdAsync('170:1357');const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);try{await n.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'OVERLAY',transition:null,resetVideoPosition:false}]}]);out.push({id,ok:true});}catch(e){out.push({id,ok:false,reason:String(e).slice(0,300)})}}
return out;
