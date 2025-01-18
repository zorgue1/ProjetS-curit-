from PIL import Image
def text_to_binary(text):
    return ''.join(format(ord(c), '08b') for c in text)


def hide_data_in_image(input_path, output_path, data):
    img = Image.open(input_path)
    img = img.convert("RGB")
    pixels = img.load()

    hidden_msg = data + "###EOF###"
    binary_msg = text_to_binary(hidden_msg)
    msg_len = len(binary_msg)


    width, height = img.size

    idx = 0
    for y in range(height):
        for x in range(width):
            if idx >= msg_len:
                break

            r, g, b = pixels[x, y]
            new_r = (r & 0xFE) | int(binary_msg[idx]) if idx < msg_len else r
            idx += 1
            if idx < msg_len:
                new_g = (g & 0xFE) | int(binary_msg[idx])
            else:
                new_g = g
            idx += 1
            if idx < msg_len:
                new_b = (b & 0xFE) | int(binary_msg[idx])
            else:
                new_b = b
            idx += 1

            pixels[x, y] = (new_r, new_g, new_b)
            if idx >= msg_len:
                break

        if idx >= msg_len:
            break

    img.save(output_path)
    print(f"The information has been successfully hidden in {output_path}")


if __name__ == "__main__":

    FLAG = "FLAG{HARD_CHALLENGE_FLAG}"
    hide_data_in_image("original.png", "challenge.png", FLAG)
