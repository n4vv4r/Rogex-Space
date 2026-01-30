import * as THREE from "three";
import { getFresnelMat } from "./fresnelMat.js";

const sunVertexShader = `
    varying vec2 vUv;
    varying vec3 vNormal;

    void main() {
        vUv = uv;
        vNormal = normalize(normalMatrix * normal);
        gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
    }
`;

const sunFragmentShader = `
    uniform sampler2D map;
    uniform float time;

    varying vec2 vUv;
    varying vec3 vNormal;

    // Ruido simple
    float hash(vec2 p) {
        return fract(sin(dot(p, vec2(12.9898, 78.233))) * 43758.5453);
    }

    float noise(vec2 p) {
        vec2 i = floor(p);
        vec2 f = fract(p);

        float a = hash(i);
        float b = hash(i + vec2(1.0, 0.0));
        float c = hash(i + vec2(0.0, 1.0));
        float d = hash(i + vec2(1.0, 1.0));

        vec2 u = f * f * (3.0 - 2.0 * f);

        return mix(a, b, u.x) +
               (c - a) * u.y * (1.0 - u.x) +
               (d - b) * u.x * u.y;
    }

    void main() {
        // Distorsión turbulenta
        float n = noise(vUv * 6.0 + time * 0.5) * 0.15;

        vec2 uvDistorted = vUv + vec2(n, n);

        vec4 tex = texture2D(map, uvDistorted);

        // Emisión vibrante
        vec3 glow = tex.rgb * (1.5 + sin(time * 2.0) * 0.2);

        gl_FragColor = vec4(glow, 1.0);
    }
`;

export async function createSun() {
    const geometry = new THREE.SphereGeometry(1, 128, 128);

    const sunTex = new THREE.TextureLoader().load("./assets/textures/sun_4k.jpg");
    sunTex.wrapS = sunTex.wrapT = THREE.RepeatWrapping;

    const sunMaterial = new THREE.ShaderMaterial({
        uniforms: {
            map: { value: sunTex },
            time: { value: 0 }
        },
        vertexShader: sunVertexShader,
        fragmentShader: sunFragmentShader
    });

    const sunMesh = new THREE.Mesh(geometry, sunMaterial);

    const fresnel = getFresnelMat({
        rimHex: 0xffaa55,
        facingHex: 0x000000
    });

    const glowMesh = new THREE.Mesh(geometry, fresnel);
    glowMesh.scale.setScalar(1.25);

    const sunlight = new THREE.PointLight(0xffddaa, 2.5, 200);
    sunlight.castShadow = false;

    const sunGroup = new THREE.Group();
    sunGroup.add(sunMesh);
    sunGroup.add(glowMesh);
    sunGroup.add(sunlight);

    sunGroup.tick = (delta) => {
        sunMaterial.uniforms.time.value += delta;
    };

    return sunGroup;
}
