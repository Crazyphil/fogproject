<?php
class DashboardPage extends FOGPage {
    public $node = 'home';
    public function __construct($name = '') {
        $this->name = 'Dashboard';
        parent::__construct($this->name);
        if ($_REQUEST[id]) $this->obj = $this->getClass(StorageNode,$_REQUEST[id]);
        $this->menu = array();
        $this->subMenu = array();
        $this->notes = array();
    }
    public function index() {
        $hostPend = '<i class="fa fa-circle fa-1x notifier"></i>&nbsp;'._('There are pending hosts awaiting approval').'<br/>'._('Click').' '.'<a href="?node=host&sub=pending">'._('here').'</a> '._('to go to the approval page');
        $macPend = '<i class="fa fa-circle fa-1x notifier"></i>&nbsp;'._('There are pending macs awaiting approval').'<br/>'._('Click').' '.'<a href="?node=report&sub=pend-mac">'._('here').'</a> '._('to go to the approval page');
        if ($_SESSION['Pending-Hosts'] && $_SESSION['Pending-MACs']) $this->FOGCore->setMessage($hostPend.'<br/>'.$macPend);
        else if ($_SESSION['Pending-Hosts']) $this->FOGCore->setMessage($hostPend);
        else if ($_SESSION['Pending-MACs']) $this->FOGCore->setMessage($macPend);
        $SystemUptime = $this->FOGCore->SystemUptime();
        $fields = array(
            _('Username') => $this->FOGUser ? $this->FOGUser->get(name) : '',
            _('Web Server') => $this->FOGCore->getSetting(FOG_WEB_HOST),
            _('TFTP Server') => $this->FOGCore->getSetting(FOG_TFTP_HOST),
            _('Load Average') => $SystemUptime[load],
            _('System Uptime') => $SystemUptime[uptime],
        );
        $this->templates = array(
            '${field}',
            '${fielddata}',
        );
        $this->attributes = array(
            array(),
            array(),
        );
        echo '<ul id="dashboard-boxes"><li><h4>'._('System Overview').'</h4>';
        foreach ((array)$fields AS $field => &$fielddata) {
            $this->data[] = array(
                'field' => $field,
                'fielddata' => $fielddata,
            );
        }
        unset($fielddata);
        $this->HookManager->processEvent(DashboardData,array(data=>&$this->data,templates=>&$this->templates,attributes=>&$this->attributes));
        $this->render();
        echo '</li><li><h4>'._('System Activity').'</h4><div class="graph pie-graph" id="graph-activity"></div></li><li><h4>'._('Disk Information').'</h4><div id="diskusage-selector">';
        $Nodes = $this->getClass(StorageNodeManager)->find(array(isEnabled=>1,isGraphEnabled=>1));
        foreach ($Nodes AS $i => &$StorageNode) {
            $curroot = trim(trim($StorageNode->get(webroot),'/'));
            $webroot = '/'.(strlen($curroot) > 1 ? $curroot.'/' : '');
            $version = $this->FOGURLRequests->process($StorageNode->get(ip).$webroot.'/service/getversion.php','GET');
            $options .= '<option value="'.$StorageNode->get(id).'">'.$StorageNode->get(name).($StorageNode->get(isMaster) == 1 ? " * " : ' ')."(${version[0]})".'</option>';
        }
        unset($StorageNode);
        echo ($options ? '<select name="storagesel" style="whitespace: no-wrap; width: 100px; position: relative; top: 100px;">'.$options.'</select>' : null);
        echo '</div><a href="?node=hwinfo"><div class="graph pie-graph" id="graph-diskusage"></div></a></li></ul><h3>'._('Imaging over the last 30 days').'</h3><div id="graph-30day" class="graph"></div><h3 id="graph-bandwidth-title">'.$this->foglang[Bandwidth].'- <span>'.$this->foglang[Transmit].'</span><!-- (<span>2 Minutes</span>)--></h3><div id="graph-bandwidth-filters"><div><a href="#" id="graph-bandwidth-filters-transmit" class="l active">'.$this->foglang[Transmit].'</a><a href="#" id="graph-bandwidth-filters-receive" class="l">'.$this->foglang[Receive].'</a></div><div class="spacer"></div><div><a href="#" rel="3600" class="r">'._('1 hour').'</a><a href="#" rel="1800" class="r">'._('30 Minutes').'</a><a href="#" rel="600" class="r">'._('10 Minutes').'</a><a href="#" rel="120" class="r active">'._('2 Minutes').'</a></div></div><div id="graph-bandwidth" class="graph"></div>';
        for ($i = 0; $i <= 30; $i++) $Graph30dayData .= '["'.(1000*$this->nice_date()->modify("-$i days")->getTimestamp()).'", '.$this->getClass(ImagingLogManager)->count(array(start=>$this->nice_date()->modify("-$i days")->format('Y-m-d%'),finish=>$this->nice_date()->modify("-$i days")->format('Y-m-d%')),'OR').']'.($i < 30 ? ', ' : '');
        $ActivityActive = 0;
        $ActivityQueued = 0;
        $ActivitySlots = 0;
        $ActivityTotalClients = 0;
        $Nodes = $this->getClass(StorageNodeManager)->find(array(isEnabled=>1));
        foreach($Nodes AS $i => &$StorageNode) {
            if ($StorageNode->isValid()) {
                $ActivityActive += $StorageNode->getUsedSlotCount();
                $ActivityQueued += $StorageNode->getQueuedSlotCount();
                $ActivityTotalClients += $StorageNode->get(maxClients);
            }
        }
        unset($StorageNode);
        $ActivitySlots = $ActivityTotalClients -  $ActivityActive - $ActivityQueued;
        echo '<div class="fog-variable" id="ActivityActive">'.$ActivityActive.'</div><div class="fog-variable" id="ActivityQueued">'.$ActivityQueued.'</div><div class="fog-variable" id="ActivitySlots">'.($ActivitySlots < 0 ? 0 : $ActivitySlots).'</div><!-- Variables --><div class="fog-variable" id="Graph30dayData">['.$Graph30dayData.']</div>';
    }
    /** bandwidth()
     * Display's the bandwidth bar on the dashboard page.
     */
    public function bandwidth() {
        $Nodes = $this->getClass(StorageNodeManager)->find(array(isGraphEnabled=>1));
        // Loop each storage node -> grab stats
        foreach($Nodes AS $i => &$StorageNode) {
            $fetchedData = $this->FOGURLRequests->process(sprintf('http://%s/%s?dev=%s',$StorageNode->get(ip),ltrim($this->FOGCore->getSetting(FOG_NFS_BANDWIDTHPATH),'/'), $StorageNode->get('interface')),'GET');
            foreach((array)$fetchedData AS $i => &$dataSet) {
                if (preg_match('/(.*)##(.*)/U', $dataSet,$match)) $data[$StorageNode->get(name)] = array(rx=>$match[1],tx=>$match[2]);
                else $data[$StorageNode->get(name)] = json_decode($dataSet,true);
            }
            unset($dataSet);
        }
        unset($StorageNode);
        echo json_encode((array)$data);
    }
    /** diskusage()
     * Display's the disk usage graph on the dashboard page.
     */
    public function diskusage() {
        // Get the node ID -> grab the ino:
        if ($this->obj->get(isGraphEnabled)) {
            try {
                $curroot = trim(trim($this->obj->get(webroot),'/'));
                $webroot = '/'.(strlen($curroot) > 1 ? $curroot.'/' : '');
                $URL = sprintf('http://%s%sstatus/freespace.php?path=%s',$this->obj->get(ip),$webroot,base64_encode($this->obj->get(path)));
                if ($Response = $this->FOGURLRequests->process($URL,'GET')) {
                    // Legacy client
                    if (preg_match('#(.*)@(.*)#', $Response[0], $match)) $Data = array(free=>$match[1],used=>$match[2]);
                    else {
                        $Response = json_decode($Response[0], true);
                        $Data = array(free=>$Response[free],used=>$Response[used]);
                    }
                } else throw new Exception('Failed to connect to '.$this->obj->get(name));
            } catch (Exception $e) {
                $Data[error] = $e->getMessage();
            }
        }
        echo json_encode((array)$Data);
    }
    /** clientCount()
     * Display's the current client count on the activity graph
     */
    public function clientcount() {
        $ActivityActive = $ActivityQueued = $ActivityTotalClients = 0;
        if ($this->obj instanceof StorageNode) {
            $Nodes = $this->getClass(StorageNodeManager)->find(array(isEnabled=>1,storageGroupID=>$this->obj->get(storageGroupID)));
            foreach($Nodes AS $i => &$SN) {
                if ($SN->isValid()) {
                    $ActivityActive += $SN->getUsedSlotCount();
                    $ActivityQueued += $SN->getQueuedSlotCount();
                    $ActivityTotalClients += $SN->get(maxClients) - $SN->getUsedSlotCount();
                }
            }
            unset($SN);
        }
        $data = array(
            ActivityActive=>$ActivityActive,
            ActivityQueued=>$ActivityQueued,
            ActivitySlots=>$ActivityTotalClients,
        );
        echo json_encode($data);
    }
}
