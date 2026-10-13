const get=async id=>await figma.getNodeByIdAsync(id);const to=await get('170:1357');const out=[];
for(const id of ['219:2824','219:2825','219:2975','219:2976']){const n=await get(id);try{await n.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'OVERLAY',transition:null,resetVideoPosition:false}]}]);out.push({id,ok:true});}catch(e){out.push({id,ok:false,reason:String(e).slice(0,300)})}}
return out;
