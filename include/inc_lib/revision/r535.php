<?php
/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2017, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 *
 **/


// Revision 535 Update Check
function phpwcms_revision_r535() {

	$status = true;

	// do former revision check – fallback to r534
	if(phpwcms_revision_check_temp('534') !== true) {
		$status = phpwcms_revision_check('534');
	}

	// change type of some content related fields from TEXT to MEDIUMTEXT

	// Retrieve Type of profession name
	$result = _dbQuery("SHOW COLUMNS FROM `".DB_PREPEND."phpwcms_profession` WHERE Field='prof_name'");
	if(isset($result[0]['Type']) && strpos($result[0]['Type'], '100')) {
		$update = _dbQuery("ALTER TABLE `".DB_PREPEND."phpwcms_profession` CHANGE `prof_name` `prof_name` VARCHAR(255) NOT NULL DEFAULT ''", 'ALTER');
		if(!$update) {
			$status = false;
		}
	}

	// Change profession ' n/a'
	_dbUpdate('phpwcms_profession', array('prof_name'=>'n/a'), "prof_name=' n/a'");

	// Import new professions
	$result = _dbCount("SELECT COUNT(*) FROM `".DB_PREPEND."phpwcms_profession`");
	if($result < 25) {
		$jobs = array(
			'academic',
			'accountant',
			'actor',
			'administrative services department manager',
			'administrator',
			'administrator, IT',
			'agricultural advisor',
			'air steward',
			'air-conditioning installer or mechanic',
			'aircraft service technician',
			'ambulance driver (non paramedic)',
			'animal carer (not in farms)',
			'animator',
			'arable farm manager, field crop or vegetable',
			'arable farmer, field crop or vegetable',
			'architect',
			'architect, landscape',
			'artist',
			'asbestos removal worker',
			'assembler',
			'assembly team leader',
			'assistant',
			'author',
			'baker',
			'bank clerk (back-office)',
			'beauty therapist',
			'beverage production process controller',
			'biologist',
			'blogger',
			'boring machine operator',
			'bricklayer',
			'builder',
			'butcher',
			'car mechanic',
			'career counsellor',
			'caretaker',
			'carpenter',
			'charge nurse',
			'check-out operator',
			'chef',
			'child-carer',
			'civil engineering technician',
			'civil servant',
			'cleaning supervisor',
			'clerk',
			'climatologist',
			'cloak room attendant',
			'cnc operator',
			'comic book writer',
			'community health worker',
			'company director',
			'computer programmer',
			'confectionery maker',
			'construction operative',
			'cook',
			'cooling or freezing installer or mechanic',
			'critic',
			'database designer',
			'decorator',
			'dental hygienist',
			'dental prosthesis technician',
			'dentist',
			'department store manager',
			'designer',
			'designer, graphic',
			'designer, industrial',
			'designer, interface',
			'designer, interior',
			'designer, screen',
			'designer, web',
			'dietician',
			'diplomat',
			'director',
			'display designer',
			'doctor',
			'domestic housekeeper',
			'economist',
			'editor',
			'education advisor',
			'electrical engineer',
			'electrical mechanic or fitter',
			'electrician',
			'engineer',
			'engineering maintenance supervisor',
			'estate agent',
			'executive',
			'executive secretary',
			'farmer',
			'felt roofer',
			'filing clerk',
			'film director',
			'financial clerk',
			'financial services manager',
			'fire fighter',
			'first line supervisor beverages workers',
			'first line supervisor of cleaning workers',
			'fisherman',
			'fishmonger',
			'flight attendant',
			'floral arranger',
			'food scientist',
			'garage supervisor',
			'garbage man',
			'gardener, all other',
			'general practitioner',
			'geographer',
			'geologist',
			'hairdresser',
			'head groundsman',
			'head teacher',
			'horse riding instructor',
			'hospital nurse',
			'hotel manager',
			'house painter',
			'hr manager',
			'it applications programmer',
			'it systems administrator',
			'jeweller',
			'journalist',
			'judge',
			'juggler',
			'kitchen assistant',
			'lathe setter-operator',
			'lawyer',
			'lecturer',
			'legal secretary',
			'lexicographer',
			'library assistant',
			'local police officer',
			'logistics manager',
			'machine tool operator',
			'magician',
			'makeup artist',
			'manager',
			'manager, all other health services',
			'marketing manager',
			'meat processing operator',
			'mechanical engineering technician',
			'medical laboratory technician',
			'medical radiography equipment operator',
			'metal moulder',
			'metal production process operator',
			'meteorologist',
			'midwifery professional',
			'miner',
			'mortgage clerk',
			'musical instrument maker',
			'musician',
			'non-commissioned officer armed forces',
			'nurse',
			'nursery school teacher',
			'nursing aid',
			'ophthalmic optician',
			'optician',
			'painter',
			'payroll clerk',
			'personal assistant',
			'personal carer in an institution for the elderly',
			'personal carer in an institution for the handicapped',
			'personal carer in private homes',
			'personnel clerk',
			'pest controller',
			'photographer',
			'physician assistant',
			'pilot',
			'pipe fitter',
			'plant maintenance mechanic',
			'plumber',
			'police inspector',
			'police officer',
			'policy advisor',
			'politician',
			'porter',
			'post secondary education teacher',
			'post sorting or distributing clerk',
			'power plant operator',
			'primary school head',
			'primary school teacher',
			'printer',
			'printing machine operator',
			'prison officer / warder',
			'product manager',
			'professional gambler',
			'project manager',
			'programmer',
			'psychologist',
			'puppeteer',
			'quality inspector, all other products',
			'receptionist',
			'restaurant cook',
			'road paviour',
			'roofer',
			'sailor',
			'sales assistant, all other',
			'sales or marketing manager',
			'sales representative',
			'sales support clerk',
			'salesperson',
			'scientist',
			'seaman (armed forces)',
			'secondary school manager',
			'secondary school teacher',
			'secretary',
			'security guard',
			'sheet metal worker',
			'ship mechanic',
			'shoe repairer, leather repairer',
			'shop assistant',
			'sign language Interpreter',
			'singer',
			'social media manager',
			'social photographer',
			'software analyst',
			'software developer',
			'software engineer',
			'soldier',
			'solicitor',
			'speech therapist',
			'steel fixer',
			'stockman',
			'structural engineer',
			'student',
			'surgeon',
			'surgical footwear maker',
			'swimming instructor',
			'system operator',
			'tailor',
			'tailor, seamstress',
			'tax inspector',
			'taxi driver',
			'teacher',
			'telephone operator',
			'telephonist',
			'theorist',
			'tile layer',
			'translator',
			'transport clerk',
			'travel agency clerk',
			'travel agent',
			'truck driver long distances',
			'trucker',
			'TV cameraman',
			'TV presenter',
			'university professor',
			'university researcher',
			'vet',
			'veterinary practitioner',
			'vocational education teacher',
			'waiter',
			'waiting staff',
			'web designer',
			'web developer',
			'webmaster',
			'welder, all other',
			'wood processing plant operator',
			'writer',
			'other',
			'n/a'
		);
		foreach($jobs as $job) {
			$sql = 'INSERT IGNORE INTO `'.DB_PREPEND.'phpwcms_profession` (prof_name) VALUES('._dbEscape($job).')';
			_dbQuery($sql, 'INSERT');
		}
	}

	return $status;
}
