from PIL import Image

def extract_data_from_image(image_path):
    img = Image.open(image_path).convert("RGB")
    pixels = img.load()
    width, height = img.size

    extracted_bits = []

    for y in range(height):
        for x in range(width):
            r, g, b = pixels[x, y]
            extracted_bits.append(str(r & 1))
            extracted_bits.append(str(g & 1))
            extracted_bits.append(str(b & 1))

    all_bits = "".join(extracted_bits)

    bytes_array = [all_bits[i:i + 8] for i in range(0, len(all_bits), 8)]

    message = ""
    for byte in bytes_array:
        message += chr(int(byte, 2))
        if "###EOF###" in message:
            message = message[:message.index("###EOF###")]
            break

    return message


if __name__ == "__main__":
    secret_message = extract_data_from_image("challenge.png")
    print("Extracted Flag:", secret_message)
