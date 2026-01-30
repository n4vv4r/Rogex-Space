<?php // simple viewer page - no server-side logic required ?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Visualizador 3D - Planetas</title>
	<style>
		html, body {
			height: 100%;
			margin: 0;
			color: #fff;
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
			overflow: hidden;
			background-image: url('assets/textures/8k_stars_milky_way.jpg');
			background-size: cover;
			background-position: center center;
			background-repeat: no-repeat;
			background-attachment: fixed;
			background-color: #000; /* fallback */
		}

		#canvas-container {
			position: absolute;
			inset: 0;
			width: 100%;
			height: 100%;
			display: flex;
			align-items: center;
			justify-content: center;
			pointer-events: auto;
		}

		canvas { display: block; position: absolute; inset: 0; z-index: 1; pointer-events: auto; }

		#ui {
			position: fixed;
			top: 20px;
			left: 20px;
			z-index: 10;
			padding: 1rem 1.4rem;
			border-radius: 20px;
			background: rgba(20,20,20,0.35);
			backdrop-filter: blur(18px) saturate(180%);
			-webkit-backdrop-filter: blur(18px) saturate(180%);
			border: 1px solid rgba(255,255,255,0.2);
			box-shadow: 0 8px 32px rgba(0,0,0,0.5);
			display: flex;
			flex-direction: column;
			gap: 0.8rem;
			min-width: 180px;
		}

		#ui label {
			font-weight: 500;
			color: #ddd;
			margin-bottom: 0.2rem;
			font-size: 0.95rem;
		}

		#planetSelect {
			padding: 0.5rem 0.8rem;
			border-radius: 9999px;
			border: 1px solid rgba(255,255,255,0.3);
			background: rgba(30,30,30,0.6);
			color: #fff;
			font-size: 0.95rem;
			cursor: pointer;
			transition: all 0.25s ease;
			appearance: none;
			text-align-last: center;
		}

		.controls-row{display:flex;gap:0.6rem;align-items:center;justify-content:center}
		.controls-row button{background:linear-gradient(180deg,#ff928f,#ff928f);border:none;color:#fff;padding:0.45rem 0.8rem;border-radius:9999px;cursor:pointer;font-weight:600;box-shadow:0 6px 18px rgba(130, 43, 43, 0.28)}
		.controls-row button:hover{transform:translateY(-2px)}
		label.toggle{display:flex;align-items:center;gap:0.4rem;color:#cfeee6;font-weight:600}
		label.toggle input{width:1rem;height:1rem}

		.slider-row{display:flex;flex-direction:column;gap:0.3rem;align-items:stretch}
		.slider-row label{font-size:0.85rem;color:#cfeee6}
		#speed{width:100%;accent-color:#a15555ff}

		#planetSelect:hover {
			background: rgba(30,30,30,0.85);
			box-shadow: 0 0 12px rgba(255, 0, 0, 0.4);
		}

		#status {
			font-size: 0.85rem;
			color: #ccc;
			min-height: 1.2em;
			text-align: center;
		}

		#credit {
			position: fixed;
			right: 12px;
			bottom: 12px;
			font-size: 12px;
			opacity: 0.7;
		}

	

		@media(max-width:600px){
			#ui { top: 12px; left: 12px; min-width: 140px; padding: 0.8rem 1rem; }
			#planetSelect { font-size: 0.9rem; }
		}
#welcome-overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(0,0,0,0.45);
  backdrop-filter: blur(20px) saturate(160%);
  -webkit-backdrop-filter: blur(20px) saturate(160%);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.6s ease;
}

.welcome-card {
  padding: 2.5rem 3rem;
  border-radius: 24px;
  background: rgba(20,20,20,0.4);
  border: 1px solid rgba(255,255,255,0.25);
  box-shadow: 0 12px 40px rgba(0,0,0,0.6);
  text-align: center;
  color: #fff;
}


.welcome-card h1 {
  margin: 0 0 0.6rem;
  font-size: 2rem;
  letter-spacing: 0.04em;
}

.welcome-card p {
  opacity: 0.85;
  margin-bottom: 1.5rem;
}

.welcome-card button {
  padding: 0.9rem 2rem;
  border-radius: 9999px;
  font-weight: 700;
  letter-spacing: 0.03em;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.25s ease;

  background: rgba(255, 0, 0, 0.1);
  border: 2px solid #ff8681;
  color: #ff8681;

  box-shadow: 0 0 28px rgba(161, 69, 66, 0.3);
}
.welcome-card button:hover {
  background: rgba(255, 0, 0, 0.2);
  box-shadow: 0 0 40px rgba(255, 134, 129, 0.5);
  transform: translateY(-2px);
}
.welcome-card button:active {
  transform: translateY(0);
  box-shadow: 0 0 18px rgba(255, 134, 129, 0.35);
}


#music-panel {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 20;
  padding: 0.6rem 1rem;
  border-radius: 9999px;
  background: rgba(20,20,20,0.35);
  backdrop-filter: blur(18px) saturate(160%);
  border: 1px solid rgba(255,255,255,0.2);
  box-shadow: 0 8px 30px rgba(0,0,0,0.45);
}

#music-panel button {
  background: none;
  border: none;
  color: #cfeee6;
  font-weight: 600;
  cursor: pointer;
}
#music-panel {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

#volumeSlider {
  width: 80px;
  accent-color: #a15555ff;
  cursor: pointer;
}

	</style>
</head>
<body>
<div id="welcome-overlay">
  <div class="welcome-card">
    <h1><img src="rogexspacetitle.png" width="350px"></h1>
    <p>Explore el sistema solar en 3D. Esto es solo un prototipo.<br>Puede tardar un poco cargar algunas texturas, aún está en desarrollo.</p>
    <button id="startExperience">Cargar</button>
  </div>
</div>

<audio id="bg-music" loop preload="auto">
  <source src="assets/audio/space_music.mp3" type="audio/mpeg">
</audio>

<div id="music-panel">
  <button id="musicToggle">⏸ Música</button>
  <input
    id="volumeSlider"
    type="range"
    min="0"
    max="100"
    value="40"
    title="Volumen"
  >
</div>


	<div id="ui">
		<label for="planetSelect">Selecciona un planeta:</label>
		<select id="planetSelect">
			<option value="earth" selected>Tierra</option>
			<option value="venus">Venus</option>
			<option value="moon">Luna</option>
			<option value="mercury">Mercurio</option>
			<option value="sun">Sol</option>
			<option value="jupiter">Júpiter</option>
			<option value="neptune">Neptuno</option>
			<option value="uranus">Urano</option>
			<option value="mars">Marte</option>
			<option value="saturn">Saturno</option>



		</select>
		<div id="status"></div>
		<div class="controls-row">
			<button id="resetView" title="Restablecer cámara">Restablecer vista</button>
			<label class="toggle"><input type="checkbox" id="autoRotate"><span>Auto-rotar</span></label>
		</div>
		<div class="slider-row">
			<label for="speed">Velocidad</label>
			<input id="speed" type="range" min="0" max="5" step="0.1" value="1">
		</div>
	</div>

	<div id="canvas-container"></div>
	<div id="credit">Texturas: NASA (cuando estén disponibles) · Fallbacks públicos</div>
	<script type="importmap">
		{
			"imports": {
				"three": "https://unpkg.com/three@0.152.2/build/three.module.js"
			}
		}
	</script>

	<script type="module">
		import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.161/build/three.module.js';
		import { OrbitControls } from 'https://cdn.jsdelivr.net/npm/three@0.161/examples/jsm/controls/OrbitControls.js';
		import getStarfield from './assets/js/getStarfield.js';
		import { getFresnelMat } from './assets/js/getFresnelMat.js';
		

		const w = window.innerWidth;
		const h = window.innerHeight;
		const scene = new THREE.Scene();
		const camera = new THREE.PerspectiveCamera(75, w / h, 0.1, 1000);
		camera.position.z = 5;
		const renderer = new THREE.WebGLRenderer({ antialias: true });
		renderer.setSize(w, h);
		document.getElementById('canvas-container').appendChild(renderer.domElement);
		
		renderer.toneMapping = THREE.ACESFilmicToneMapping;
		renderer.outputColorSpace = THREE.LinearSRGBColorSpace;

		const loader = new THREE.TextureLoader();
		const starTexture = loader.load('./assets/textures/8k_stars_milky_way.jpg', () => {
    const bgMat = new THREE.MeshBasicMaterial({ map: starTexture });
    scene.background = starTexture; 
});

		let currentPlanet = null;
		let controls = new OrbitControls(camera, renderer.domElement);

		async function createEarth() {
			const detail = 12;
			const geometry = new THREE.IcosahedronGeometry(1, detail);
			
			const material = new THREE.MeshPhongMaterial({
				map: loader.load("./assets/textures/earth_10k.jpg"),
				specularMap: loader.load("./assets/textures/02_earthspec1k.jpg"),
				bumpMap: loader.load("./assets/textures/01_earthbump1k.jpg"),
				bumpScale: 0.04,
			});
			const earthMesh = new THREE.Mesh(geometry, material);

			const lightsMat = new THREE.MeshBasicMaterial({
				map: loader.load("./assets/textures/earth_night_10k.jpg"),
				blending: THREE.AdditiveBlending,
			});
			const lightsMesh = new THREE.Mesh(geometry, lightsMat);
			lightsMesh.name = 'lights';

			const cloudsMat = new THREE.MeshStandardMaterial({
				map: loader.load("./assets/textures/earth_clouds.jpg"),
				transparent: true,
				opacity: 0.8,
				blending: THREE.AdditiveBlending,
				alphaMap: loader.load('./assets/textures/earth_clouds_4k.jpg'),
			});
			const cloudsMesh = new THREE.Mesh(geometry, cloudsMat);
			cloudsMesh.scale.setScalar(1.003);
			cloudsMesh.name = 'clouds';

			const fresnelMat = getFresnelMat();
			const glowMesh = new THREE.Mesh(geometry, fresnelMat);
			glowMesh.scale.setScalar(1.01);

			const earthGroup = new THREE.Group();
			earthGroup.rotation.z = -23.4 * Math.PI / 180;
			earthGroup.add(earthMesh);
			earthGroup.add(lightsMesh);
			earthGroup.add(cloudsMesh);
			earthGroup.add(glowMesh);

			return earthGroup;
		}


	async function createVenus() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const venusSurface = loader.load("./assets/textures/venus_2k.jpg");

    const venusClouds = loader.load(
        "./assets/textures/venus_clouds_2k.jpg"
	);

    const surfaceMat = new THREE.MeshStandardMaterial({
        map: venusSurface,
        roughness: 1.0,
        metalness: 0.0
    });

    const surfaceMesh = new THREE.Mesh(geometry, surfaceMat);

    const cloudsMat = new THREE.MeshStandardMaterial({
        map: venusClouds,
        transparent: true,
        opacity: 0.9,
        blending: THREE.AdditiveBlending
    });

    const cloudsMesh = new THREE.Mesh(geometry, cloudsMat);
    cloudsMesh.scale.setScalar(1.015);
    cloudsMesh.name = "clouds";

    const fresnelMat = getFresnelMat({
        rimHex: 0xE7C36F,
        facingHex: 0x000000
    });

    const glowMesh = new THREE.Mesh(geometry, fresnelMat);
    glowMesh.scale.setScalar(1.06);

    const venusGroup = new THREE.Group();
    venusGroup.add(surfaceMesh);
    venusGroup.add(cloudsMesh);
    venusGroup.add(glowMesh);

    return venusGroup;
}

async function createMoon() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const moonTexture = loader.load("./assets/textures/8k_moon.jpg");

const moonMat = new THREE.MeshStandardMaterial({
    map: moonTexture,
    bumpMap: loader.load("./assets/textures/moon_bump_2k.jpg"),
    bumpScale: 0.07,
    roughness: 1.0,
    metalness: 0.0
});

    const moonMesh = new THREE.Mesh(geometry, moonMat);



    const moonGroup = new THREE.Group();
    moonGroup.add(moonMesh);

    return moonGroup;
}

async function createMercury() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const mercuryTexture = loader.load("./assets/textures/mercury_4k.jpg");

    const mercuryMat = new THREE.MeshStandardMaterial({
        map: mercuryTexture,
        roughness: 1.0,
        metalness: 0.0
    });

    const mercuryMesh = new THREE.Mesh(geometry, mercuryMat);

    const mercuryGroup = new THREE.Group();
    mercuryGroup.add(mercuryMesh);

    return mercuryGroup;
}
async function createSun() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const sunTexture = loader.load("./assets/textures/8k_sun.jpg");

    const sunMat = new THREE.MeshBasicMaterial({
        map: sunTexture,
        emissive: new THREE.Color(0xffffff),
        emissiveIntensity: 1.5
    });

    const sunMesh = new THREE.Mesh(geometry, sunMat);

    const fresnelMat = getFresnelMat({
        rimHex: 0xffdd66,
        facingHex: 0x000000
    });

    const glowMesh = new THREE.Mesh(geometry, fresnelMat);
    glowMesh.scale.setScalar(1.1);

    const sunGroup = new THREE.Group();
    sunGroup.add(sunMesh);
    sunGroup.add(glowMesh);

    return sunGroup;
}

async function createJupiter() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const jupiterTexture = loader.load("./assets/textures/jupiter_2k.jpg");

    const surfaceMat = new THREE.MeshStandardMaterial({
        map: jupiterTexture,
        roughness: 1.0,
        metalness: 0.0
    });

    const surfaceMesh = new THREE.Mesh(geometry, surfaceMat);

    const fresnelMat = getFresnelMat({
        rimHex: 0xffcfa0,
        facingHex: 0x000000
    });
    const glowMesh = new THREE.Mesh(geometry, fresnelMat);
    glowMesh.scale.setScalar(1.02);

    const jupiterGroup = new THREE.Group();
    jupiterGroup.add(surfaceMesh);
    jupiterGroup.add(glowMesh);

    return jupiterGroup;
}

async function createNeptune() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const neptuneTexture = loader.load("./assets/textures/neptune_2k.jpg");

    const surfaceMat = new THREE.MeshStandardMaterial({
        map: neptuneTexture,
        roughness: 1.0,
        metalness: 0.0
    });

    const surfaceMesh = new THREE.Mesh(geometry, surfaceMat);

    const fresnelMat = getFresnelMat({
        rimHex: 0x3399ff,
        facingHex: 0x000000
    });

    const glowMesh = new THREE.Mesh(geometry, fresnelMat);
    glowMesh.scale.setScalar(1.02);

    const neptuneGroup = new THREE.Group();
    neptuneGroup.add(surfaceMesh);
    neptuneGroup.add(glowMesh);

    return neptuneGroup;
}

async function createUranus() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const uranusTexture = loader.load("./assets/textures/uranus_2k.jpg");

    const surfaceMat = new THREE.MeshStandardMaterial({
        map: uranusTexture,
        roughness: 1.0,
        metalness: 0.0
    });

    const surfaceMesh = new THREE.Mesh(geometry, surfaceMat);

    const fresnelMat = getFresnelMat({
        rimHex: 0x66ccff,
        facingHex: 0x000000
    });

    const glowMesh = new THREE.Mesh(geometry, fresnelMat);
    glowMesh.scale.setScalar(1.02);

    const uranusGroup = new THREE.Group();
    uranusGroup.add(surfaceMesh);
    uranusGroup.add(glowMesh);

    return uranusGroup;
}

async function createMars() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const marsTexture = loader.load("./assets/textures/mars_2k.jpg");

    const surfaceMat = new THREE.MeshStandardMaterial({
        map: marsTexture,
        roughness: 1.0,
        metalness: 0.0
    });

    const surfaceMesh = new THREE.Mesh(geometry, surfaceMat);

    const fresnelMat = getFresnelMat({
        rimHex: 0xffa066,
        facingHex: 0x000000
    });
    const glowMesh = new THREE.Mesh(geometry, fresnelMat);
    glowMesh.scale.setScalar(1.02);

    const marsGroup = new THREE.Group();
    marsGroup.add(surfaceMesh);
    marsGroup.add(glowMesh);

    return marsGroup;
}

async function createSaturn() {
    const detail = 12;
    const geometry = new THREE.IcosahedronGeometry(1, detail);

    const saturnTexture = loader.load("./assets/textures/saturn_2k.jpg");
    const surfaceMat = new THREE.MeshStandardMaterial({
        map: saturnTexture,
        roughness: 1.0,
        metalness: 0.0
    });
    const surfaceMesh = new THREE.Mesh(geometry, surfaceMat);

    const fresnelMat = getFresnelMat({
        rimHex: 0xffe5b4,
        facingHex: 0x000000
    });
    const glowMesh = new THREE.Mesh(geometry, fresnelMat);
    glowMesh.scale.setScalar(1.02);

    // anillos (hay que arreglar el movimiento de los anillos)
    const ringTexture = loader.load("./assets/textures/saturn_ring_alpha_2k.png");
    ringTexture.wrapS = THREE.RepeatWrapping;
    ringTexture.wrapT = THREE.ClampToEdgeWrapping;

    const innerRadius = 1.2;
    const outerRadius = 2;
    const ringSegments = 256;
    const ringGeometry = new THREE.RingGeometry(innerRadius, outerRadius, ringSegments);

    const posAttr = ringGeometry.attributes.position;
    const uvAttr = ringGeometry.attributes.uv;
    const centerRadius = (innerRadius + outerRadius) / 2;

    for (let i = 0; i < posAttr.count; i++) {
        const x = posAttr.getX(i);
        const y = posAttr.getY(i);
        const dist = Math.sqrt(x * x + y * y);

        const u = dist < centerRadius ? 0 : 1;
        uvAttr.setXY(i, u, 1);
    }
    uvAttr.needsUpdate = true;

    const ringMat = new THREE.MeshBasicMaterial({
        map: ringTexture,
        side: THREE.DoubleSide,
        transparent: true
    });

    const ringMesh = new THREE.Mesh(ringGeometry, ringMat);
    ringMesh.rotation.x = -Math.PI / 2;

    const saturnGroup = new THREE.Group();
    saturnGroup.add(surfaceMesh);
    saturnGroup.add(glowMesh);
    saturnGroup.add(ringMesh);

    return saturnGroup;
}



		const stars = getStarfield({numStars: 2000});
		scene.add(stars);

		const sunLight = new THREE.DirectionalLight(0xffffff, 2.0);
		sunLight.position.set(-2, 0.5, 1.5);
		scene.add(sunLight);

		async function showPlanet(name) {
			const statusDiv = document.getElementById('status');
			statusDiv.textContent = 'Cargando ' + name + '...';
			
			if (currentPlanet) {
				scene.remove(currentPlanet);
			}

			try {
if (name === 'earth') {
    currentPlanet = await createEarth();
    camera.position.z = 3.5;

} else if (name === 'venus') {
    currentPlanet = await createVenus();
    camera.position.z = 3.8;

} else if (name === 'moon') {
    currentPlanet = await createMoon();
    camera.position.z = 3.2;

} else if (name === 'mercury') {
    currentPlanet = await createMercury();
    camera.position.z = 3.3;

} else if (name === 'sun') {
    currentPlanet = await createSun();
    camera.position.z = 4.0;

} else if (name === 'jupiter') {
    currentPlanet = await createJupiter();
    camera.position.z = 3.8;

} else if (name === 'neptune') {
    currentPlanet = await createNeptune();
    camera.position.z = 3.9;

} else if (name === 'uranus') {
    currentPlanet = await createUranus();
    camera.position.z = 3.9;
} else if (name === 'mars') {
    currentPlanet = await createMars();
    camera.position.z = 3.5;

} else if (name === 'saturn') {
    currentPlanet = await createSaturn();
    camera.position.z = 4.2;
} else {
    statusDiv.textContent = 'Planeta no reconocido';
    return;
}



				scene.add(currentPlanet);
				controls.target.set(0, 0, 0);
				controls.update();
				statusDiv.textContent = '';
			} catch (err) {
				console.error('Error:', err);
				statusDiv.textContent = 'Error cargando planeta';
			}
		}

		showPlanet('earth');

		document.getElementById('planetSelect').addEventListener('change', (e) => {
			showPlanet(e.target.value);
		});

		document.getElementById('resetView').addEventListener('click', () => {
			camera.position.z = 3.5;
			controls.target.set(0, 0, 0);
			controls.update();
		});

		const autoRotateCheckbox = document.getElementById('autoRotate');
		const speedSlider = document.getElementById('speed');

		function animate() {
			requestAnimationFrame(animate);
if (currentPlanet && currentPlanet.tick) {
    const delta = clock.getDelta();
    currentPlanet.tick(delta);
}
			if (currentPlanet && autoRotateCheckbox.checked) {
				const speed = 0.002 * parseFloat(speedSlider.value || 1);
				const earthMesh = currentPlanet.children[0];
				const lightsMesh = currentPlanet.children[1];
				const cloudsMesh = currentPlanet.children[2];
				
				if (earthMesh) earthMesh.rotation.y += speed;
				if (lightsMesh) lightsMesh.rotation.y += speed;
				if (cloudsMesh) cloudsMesh.rotation.y += speed * 1.15;
			}

			controls.update();
			renderer.render(scene, camera);
		}

		animate();

		window.addEventListener('resize', () => {
			camera.aspect = window.innerWidth / window.innerHeight;
			camera.updateProjectionMatrix();
			renderer.setSize(window.innerWidth, window.innerHeight);
		});


const music = document.getElementById("bg-music");
const startBtn = document.getElementById("startExperience");
const overlay = document.getElementById("welcome-overlay");
const musicToggle = document.getElementById("musicToggle");

let musicPlaying = false;

music.volume = 0.4;

// comenzar visualess
startBtn.addEventListener("click", async () => {
  try {
    await music.play();
    musicPlaying = true;
    musicToggle.textContent = "⏸ Música";
  } catch (e) {
    console.warn("Audio bloqueado:", e);
  }

  overlay.style.opacity = "0";
  overlay.style.pointerEvents = "none";

  overlay.addEventListener("transitionend", () => {
    overlay.remove();
  }, { once: true });
});



musicToggle.addEventListener("click", () => {
  if (musicPlaying) {
    music.pause();
    musicToggle.textContent = "▶ Música";
  } else {
    music.play();
    musicToggle.textContent = "⏸ Música";
  }
  musicPlaying = !musicPlaying;
});

const volumeSlider = document.getElementById("volumeSlider");

music.volume = volumeSlider.value / 100;

volumeSlider.addEventListener("input", () => {
  const vol = volumeSlider.value / 100;
  music.volume = vol;
  localStorage.setItem("musicVolume", vol);
});
const savedVolume = localStorage.getItem("musicVolume");
if (savedVolume !== null) {
  music.volume = parseFloat(savedVolume);
  volumeSlider.value = Math.round(music.volume * 100);
}


	</script>
</body>
</html>
