export class PlanetRenderer {
	constructor(scene, THREE) {
		this.scene = scene;
		this.THREE = THREE;
		this.textureLoader = new THREE.TextureLoader();
		
		this.setupLighting();
	}

	setupLighting() {
			const ambient = new this.THREE.AmbientLight(0xffffff, 0.0);
		this.scene.add(ambient);

		const sunLight = new this.THREE.DirectionalLight(0xffffff, 1.3);
		sunLight.position.set(5, 3, 5);
		sunLight.castShadow = true;
			this.scene.add(sunLight);
			this.sunLight = sunLight;
	}

	async loadTexture(urlPrimary, urlFallback = null) {
		return new Promise((resolve) => {
			this.textureLoader.load(
				urlPrimary,
				(tex) => {
					console.log('✓ Loaded:', urlPrimary);
					resolve(tex);
				},
				undefined,
				(err) => {
					if (urlFallback) {
						console.warn('✗ Primary failed, trying fallback:', urlPrimary);
						this.textureLoader.load(
							urlFallback,
							(tex) => {
								console.log('✓ Loaded fallback:', urlFallback);
								resolve(tex);
							},
							undefined,
							() => {
								console.error('✗ Both failed:', urlPrimary, urlFallback);
								resolve(null);
							}
						);
					} else {
						console.warn('✗ Failed to load (no fallback):', urlPrimary);
						resolve(null);
					}
				}
			);
		});
	}

	async createEarth(radius = 1.0) {
		console.log('Creating Earth...');
		const textures = {
			day: await this.loadTexture(
				'assets/textures/earth_10k.jpg',
				'assets/textures/earth_fallback.jpg'
			),
			topo: await this.loadTexture('assets/textures/earth_topo_10k.jpg'),
			clouds: await this.loadTexture('assets/textures/earth_clouds_fair_4k.png'),
			ocean: await this.loadTexture('assets/textures/earth_ocean_reflectance_10k.jpg'),
			night: await this.loadTexture('assets/textures/earth_night_10k.jpg'), 
		};

		console.log('Textures loaded:', {
			day: textures.day ? '✓' : '✗',
			topo: textures.topo ? '✓' : '✗',
			clouds: textures.clouds ? '✓' : '✗',
			ocean: textures.ocean ? '✓' : '✗',
			night: textures.night ? '✓' : '✗',
		});

		const group = new this.THREE.Group();

		const dayGeo = new this.THREE.SphereGeometry(radius, 256, 256);
		console.log('Day geometry created, faces:', dayGeo.attributes.position.count);
		
		const dayMat = new this.THREE.ShaderMaterial({
			uniforms: {
				map: { value: textures.day },
				bumpMap: { value: textures.topo },
				bumpScale: { value: 0.06 },
				specularMap: { value: textures.ocean },
				nightMap: { value: textures.night },
				nightIntensity: { value: 15.0 },
				nightColor: { value: new this.THREE.Color(0x444444) },
				sunPosition: { value: new this.THREE.Vector3(5, 3, 5) },
			},
			vertexShader: `
				varying vec2 vUv;
				varying vec3 vNormal;
                
				void main() {
					vUv = uv;
					// transform normal to world space so lighting is independent of camera
					vNormal = normalize((modelMatrix * vec4(normal, 0.0)).xyz);
					gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
				}
			`,
			fragmentShader: `
				uniform sampler2D map;
				uniform sampler2D bumpMap;
				uniform sampler2D specularMap;
				uniform sampler2D nightMap;
				uniform float nightIntensity;
				uniform vec3 nightColor;
				uniform vec3 sunPosition;
				
				varying vec2 vUv;
				varying vec3 vNormal;
				
				void main() {
					vec3 dayColor = texture2D(map, vUv).rgb;
					
					vec3 nightLights = texture2D(nightMap, vUv).rgb;
					
					float nightBrightness = (nightLights.r + nightLights.g + nightLights.b) / 3.0;
					float nightLightMask = smoothstep(0.02, 0.12, nightBrightness);
					
					vec3 sunDir = normalize(sunPosition);
					
					float sunFace = dot(vNormal, sunDir);
					
			float sunlight = clamp(sunFace, 0.0, 1.0);
			
			float dayMask = smoothstep(-0.1, 0.3, sunlight);
			vec3 litColor = dayColor * dayMask * pow(sunlight, 0.6);
			
			float nightFactor = pow(1.0 - sunlight, 1.8);
			vec3 nightLightColor = nightLights * nightIntensity * nightFactor * nightLightMask;
			
			vec3 finalColor = mix(nightLightColor, litColor, dayMask);
					
					gl_FragColor = vec4(finalColor, 1.0);
				}
			`,
		});

		console.log('Shader material created for smart night lighting');
		
			if (this.sunLight && dayMat.uniforms && dayMat.uniforms.sunPosition) {
				dayMat.uniforms.sunPosition.value = this.sunLight.position;
			}
			const dayMesh = new this.THREE.Mesh(dayGeo, dayMat);
		group.add(dayMesh);

		if (textures.clouds) {
			const cloudsGeo = new this.THREE.SphereGeometry(radius * 1.008, 128, 128);
			const cloudsMat = new this.THREE.ShaderMaterial({
				uniforms: {
					map: { value: textures.clouds },
					cloudOpacity: { value: 0.6 },
					sunPosition: { value: new this.THREE.Vector3(5, 3, 5) },
				},
				vertexShader: `
					varying vec2 vUv;
					varying vec3 vNormal;
					void main() {
						vUv = uv;
						vNormal = normalize((modelMatrix * vec4(normal, 0.0)).xyz);
						gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
					}
				`,
				fragmentShader: `
					uniform sampler2D map;
					uniform float cloudOpacity;
					uniform vec3 sunPosition;
					varying vec2 vUv;
					varying vec3 vNormal;
					void main() {
						vec4 c = texture2D(map, vUv);
						vec3 sunDir = normalize(sunPosition);
						float sunFace = clamp(dot(vNormal, sunDir), 0.0, 1.0);
						float finalVisibility = pow(sunFace, 1.8);
						vec3 color = c.rgb * finalVisibility;
						float alpha = c.a * cloudOpacity * finalVisibility;
						if (alpha < 0.01) discard;
						gl_FragColor = vec4(color, alpha);
					}
				`,
				transparent: true,
				depthWrite: false
			});
				if (this.sunLight && cloudsMat.uniforms && cloudsMat.uniforms.sunPosition) {
					cloudsMat.uniforms.sunPosition.value = this.sunLight.position;
				}
				const cloudsMesh = new this.THREE.Mesh(cloudsGeo, cloudsMat);
			cloudsMesh.name = 'clouds';
			group.add(cloudsMesh);
			console.log('Clouds layer added (shader-based, darkened on night side)');
		}

		group.rotation.z = (23.44 * Math.PI) / 180;
		console.log(' Axial tilt applied: 23.44°');

		console.log('✓ Earth created successfully');
		return group;
	}

	async createVenus(radius = 0.95) {
		const texture = await this.loadTexture(
			'assets/textures/venus.png',
			'assets/textures/venus_fallback.jpg'
		);

		const geo = new this.THREE.SphereGeometry(radius, 128, 128);
		const mat = new this.THREE.MeshPhongMaterial({
			map: texture,
			emissive: new this.THREE.Color(0x1a1a00),
			emissiveIntensity: 0.15,
			shininess: 3,
		});

		const mesh = new this.THREE.Mesh(geo, mat);
		return mesh;
	}

	async createMoon(radius = 0.27) {
		const texture = await this.loadTexture(
			'assets/textures/moon.png',
			'assets/textures/moon_fallback.jpg'
		);

		const geo = new this.THREE.SphereGeometry(radius, 128, 128);
		const mat = new this.THREE.MeshPhongMaterial({
			map: texture,
			shininess: 2,
		});

		const mesh = new this.THREE.Mesh(geo, mat);
		return mesh;
	}


	disposePlanet(group) {
		if (!group) return;
		group.traverse((child) => {
			if (child.geometry) child.geometry.dispose();
			if (child.material) {
				if (Array.isArray(child.material)) {
					child.material.forEach((m) => {
						Object.values(m).forEach((val) => val?.dispose?.());
						m.dispose();
					});
				} else {
					Object.values(child.material).forEach((val) => val?.dispose?.());
					child.material.dispose();
				}
			}
		});
	}
}
