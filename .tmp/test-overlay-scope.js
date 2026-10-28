const ids=['170:1379','219:2762','166:570'];const targets=['170:1291','170:1357'];const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);for(const tid of targets){const to=await figma.getNodeByIdAsync(tid);try{await n.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'OVERLAY',transition:null,resetVideoPosition:false}]}]);out.push({id,tid,ok:true});break;}catch(e){out.push({id,tid,ok:false,reason:String(e).slice(0,180)})}}}
return out;
