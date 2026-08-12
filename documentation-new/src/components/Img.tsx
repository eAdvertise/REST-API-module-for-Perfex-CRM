import React from 'react'
import type { JSX } from 'react/jsx-runtime'


import images_9e571fd0_8fc8_43d7_a542_ef1776216259_png from '/images/9e571fd0-8fc8-43d7-a542-ef1776216259.png';

export const Img = ({ id }) => {
    switch (String(id)) {    case "0":
        return (
            <img src={images_9e571fd0_8fc8_43d7_a542_ef1776216259_png} width={"80"} height={"80"} alt={""}></img>
        );
    default:
        return null;
    }
};

export default Img
