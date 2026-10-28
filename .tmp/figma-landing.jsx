<Frame name="Landing / Welcome" w={1366} h={768} flex="row" bg="var:page-bg">
  <Frame name="Landing showcase" w={650} h="fill" flex="col" justify="between" bg="var:slate-900" p={48}>
    <Frame name="Brand" flex="row" gap={12} items="center">
      <Frame w={36} h={36} bg="var:white" rounded={10} flex="row" justify="center" items="center"><Icon name="lucide:house" size={20} color="var:slate-900" /></Frame>
      <Frame flex="col" gap={2}><Text font="Figtree" size={20} weight="bold" color="var:white">StayTrack</Text><Text font="Figtree" size={12} color="var:slate-300">Apartment Management</Text></Frame>
    </Frame>
    <Frame name="Landing copy" flex="col" gap={20} w={480}>
      <Text font="Figtree" size={13} weight="semibold" color="var:blue-200">ONE PLACE. EVERY PROPERTY DETAIL.</Text>
      <Text font="Figtree" size={44} weight="bold" color="var:white" w="fill">Run your apartments with clarity.</Text>
      <Text font="Figtree" size={17} color="var:slate-300" w="fill">Track units, tenants, leases, bills, and payments from a calm, reliable workspace built for property teams.</Text>
      <Frame name="Proof metrics" flex="row" gap={32}>
        <Frame flex="col" gap={3}><Text font="Figtree" size={28} weight="bold" color="var:white">24</Text><Text font="Figtree" size={13} color="var:slate-400">Units managed</Text></Frame>
        <Frame flex="col" gap={3}><Text font="Figtree" size={28} weight="bold" color="var:white">96%</Text><Text font="Figtree" size={13} color="var:slate-400">Occupancy</Text></Frame>
        <Frame flex="col" gap={3}><Text font="Figtree" size={28} weight="bold" color="var:white">PHP 286K</Text><Text font="Figtree" size={13} color="var:slate-400">Collected this month</Text></Frame>
      </Frame>
    </Frame>
    <Text font="Figtree" size={13} color="var:slate-400">Secure property operations for Metro Manila teams</Text>
  </Frame>
  <Frame name="Landing panel" w={716} h="fill" flex="col" justify="center" items="center" bg="var:surface">
    <Frame name="Welcome card" w={420} flex="col" gap={20}>
      <Frame flex="col" gap={8}>
        <Text font="Figtree" size={13} weight="semibold" color="var:slate-500">WELCOME</Text>
        <Text font="Figtree" size={30} weight="bold" color="var:text-strong">Your apartments, organized.</Text>
        <Text font="Figtree" size={15} color="var:text-muted" w="fill">Sign in to manage units, tenants, leases, bills, and payments from one workspace.</Text>
      </Frame>
      <Frame name="Sign in CTA" w="fill" h={44} bg="var:primary" rounded={8} flex="row" gap={8} justify="center" items="center"><Text font="Figtree" size={14} weight="semibold" color="var:white">Sign in</Text><Icon name="lucide:arrow-right" size={16} color="var:white" /></Frame>
      <Frame name="Divider" w="fill" flex="row" gap={12} items="center"><Frame h={1} grow={1} bg="var:border" /><Text font="Figtree" size={13} color="var:text-subtle">or explore another view</Text><Frame h={1} grow={1} bg="var:border" /></Frame>
      <Frame name="Register CTA" w="fill" h={44} bg="var:surface" stroke="var:border-strong" strokeWidth={1} rounded={8} flex="row" gap={8} justify="center" items="center"><Icon name="lucide:user" size={16} color="var:text" /><Text font="Figtree" size={14} weight="semibold" color="var:text">Create an account</Text></Frame>
      <Frame flex="row" justify="center" gap={5}><Text font="Figtree" size={14} color="var:text-muted">New tenant?</Text><Text font="Figtree" size={14} weight="semibold" color="var:blue-600">Create your account</Text></Frame>
    </Frame>
  </Frame>
</Frame>
